/* global Vue */
(function () {
    'use strict';

    const RECONNECT_DELAY = 5000;
    const SSE_MAX_RETRIES = 3;
    const POLL_INTERVAL = 8000;
    const SSE_RETRY_FROM_POLL = 60000;
    const MAX_HISTORY = 9;

    let eventSource = null;
    let sseRetries = 0;

    new Vue({
        el: '#app',

        data: {
            publicId: null,
            featured: null,
            history: [],
            initialized: false,
            isFlash: false,
            ticketEnter: false,
            clockTime: '00:00:00',
            clockDate: '',
        },

        computed: {
            featuredTitle() {
                return this.featured ? this.featured.senha : '---';
            },
            featuredSubtitle() {
                if (!this.featured) {
                    return '';
                }
                return this.featured.local + ' ' + this.padNum(this.featured.numeroLocal, 2);
            },
            featuredDescription() {
                return this.featured ? (this.featured.prioridade || '') : '';
            },
            featuredIsPriority() {
                return this.featured ? (this.featured.peso || 0) > 0 : false;
            },
        },

        methods: {
            padNum(n, len) {
                return String(n || 0).padStart(len, '0');
            },

            updateClock() {
                var now = new Date();
                var p = (n) => { return String(n).padStart(2, '0'); };
                this.clockTime = p(now.getHours()) + ':' + p(now.getMinutes()) + ':' + p(now.getSeconds());
                this.clockDate = now.toLocaleDateString('pt-BR', {
                    weekday: 'long', day: '2-digit', month: 'long', year: 'numeric',
                });
            },

            startPanel({ baseUrl, publicId, unidadeId, mercureUrl }) {
                this.baseUrl = baseUrl;
                this.publicId = publicId;
                this.unidadeId = unidadeId;
                this.mercureUrl = mercureUrl;
                this.connectSSE();
                this.fetchData();
            },

            fetchData() {
                fetch(this.baseUrl + '/data')
                    .then((r) => {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then((data) => {
                        if (!this.initialized) {
                            // First load: dedupe the full server response and seed history.
                            var deduped = this.dedupe(data);
                            var next = deduped.length ? deduped[0] : null;
                            this.featured = next;
                            this.history = deduped.slice(1, MAX_HISTORY + 1);
                            this.initialized = true;
                            if (next) {
                                this.triggerEffects();
                            }
                        } else {
                            // Subsequent loads: collect every record newer than the current
                            // featured — multiple calls may have happened during the interval.
                            var newRecords = this.featured
                                ? data.filter((t) => t.id > this.featured.id)
                                : data;

                            if (newRecords.length > 0) {
                                // Data arrives newest-first; process oldest-first so that
                                // featured ends up as the most recent call.
                                newRecords.slice().reverse().forEach((record) => {
                                    if (this.featured && this.dedupeKey(record) !== this.dedupeKey(this.featured)) {
                                        this.history = this.prependToHistory(this.featured, this.history);
                                    }
                                    this.featured = record;
                                });
                                this.triggerEffects();
                            }
                        }
                    })
                    .catch((e) => {
                        console.warn('[painel] fetchData:', e.message);
                    });
            },

            dedupeKey(t) {
                return [
                    t.siglaSenha,
                    t.numeroSenha,
                    t.local,
                    t.numeroLocal,
                    t.servico ? t.servico.id : '',
                ].join('|');
            },

            /**
             * Removes duplicate entries from a list of records.
             * Since data arrives ordered by id DESC, the first occurrence of
             * each key is the most recent call — duplicates are later rows.
             */
            dedupe(records) {
                const seen = new Set();
                return records.filter((t) => {
                    const key = this.dedupeKey(t);
                    if (seen.has(key)) return false;
                    seen.add(key);
                    return true;
                });
            },

            /**
             * Prepends a record to history, removing any existing entry with
             * the same dedup key so the same ticket never appears twice.
             */
            prependToHistory(record, history) {
                const key = this.dedupeKey(record);
                return [record, ...history.filter((t) => this.dedupeKey(t) !== key)]
                    .slice(0, MAX_HISTORY);
            },

            triggerEffects() {
                // Scan-line flash
                this.isFlash = false;
                this.$nextTick(() => {
                    this.isFlash = true;
                    setTimeout(() => { this.isFlash = false; }, 700);
                });
                // Ticket enter animation
                this.ticketEnter = false;
                this.$nextTick(() => {
                    this.ticketEnter = true;
                    setTimeout(() => { this.ticketEnter = false; }, 500);
                });
                // Alert sound
                this.playAlert();
            },

            playAlert() {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) {
                        return;
                    }

                    const ctx = new AudioCtx();
                    // Two-tone chime: high note → lower note
                    const tones = [
                        { freq: 880, start: 0,    dur: 0.18, gain: 0.45 },
                        { freq: 660, start: 0.2,  dur: 0.28, gain: 0.35 },
                    ];

                    let plays = 0;
                    tones.forEach((t) => {
                        var osc  = ctx.createOscillator();
                        var env  = ctx.createGain();
                        var now  = ctx.currentTime;

                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(t.freq, now + t.start);

                        // Attack → sustain → release envelope
                        env.gain.setValueAtTime(0, now + t.start);
                        env.gain.linearRampToValueAtTime(t.gain, now + t.start + 0.02);
                        env.gain.setValueAtTime(t.gain, now + t.start + t.dur - 0.06);
                        env.gain.linearRampToValueAtTime(0, now + t.start + t.dur);

                        osc.connect(env);
                        env.connect(ctx.destination);
                        osc.start(now + t.start);
                        osc.stop(now + t.start + t.dur);

                        osc.onended = () => {
                            osc.disconnect();
                            env.disconnect();
                            plays++;
                            if (plays >= tones.length) {
                                ctx.close();
                            }
                        };
                    });
                } catch (e) {
                    console.warn('[painel] playAlert:', e.message);
                }
            },

            connectSSE() {
                this.disconnectSSE();

                if (!this.mercureUrl) {
                    console.warn('[painel] mercureUrl not configured, falling back to polling');
                    this._startPolling();
                    return;
                }

                try {
                    const url = new URL(this.mercureUrl);
                    url.searchParams.append('topic', '/unidades/' + this.unidadeId + '/painel');
                    url.searchParams.append('topic', '/paineis');
                    eventSource = new EventSource(url.toString());
                    eventSource.onopen = () => {
                        sseRetries = 0;
                    };
                    eventSource.onmessage = () => {
                        sseRetries = 0;
                        this.fetchData();
                    };
                    eventSource.onerror = () => {
                        sseRetries++;
                        if (sseRetries >= SSE_MAX_RETRIES) {
                            console.warn('[painel] SSE failed after', sseRetries, 'attempts, falling back to polling');
                            this.disconnectSSE();
                            this._startPolling();
                        } else {
                            console.warn('[painel] SSE error (attempt', sseRetries, '), reconnecting in', RECONNECT_DELAY, 'ms');
                            this.disconnectSSE();
                            this._reconnectTimer = setTimeout(() => { this.connectSSE(); }, RECONNECT_DELAY);
                        }
                    };
                } catch (e) {
                    console.warn('[painel] EventSource error:', e.message);
                    this._startPolling();
                }
            },

            _startPolling() {
                this.fetchData();
                this._pollInterval = setInterval(() => { this.fetchData(); }, POLL_INTERVAL);
                // Periodically attempt SSE reconnect from polling mode
                this._sseRetryTimer = setTimeout(() => {
                    if (this._pollInterval) {
                        console.info('[painel] Retrying SSE from polling mode');
                        sseRetries = 0;
                        this.connectSSE();
                    }
                }, SSE_RETRY_FROM_POLL);
            },

            disconnectSSE() {
                if (eventSource) { 
                    eventSource.close();
                    eventSource = null;
                }
                if (this._pollInterval) { clearInterval(this._pollInterval); this._pollInterval = null; }
                if (this._reconnectTimer) { clearTimeout(this._reconnectTimer); this._reconnectTimer = null; }
                if (this._sseRetryTimer) { clearTimeout(this._sseRetryTimer); this._sseRetryTimer = null; }
            },
        },

        mounted() {
            this.updateClock();
            setInterval(() => { this.updateClock(); }, 1000);

            this.startPanel({...this.$el.dataset});
        },

        beforeDestroy() {
            this.disconnectSSE();
        },
    });
}());
