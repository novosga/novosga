import { useState, useEffect, useCallback, useRef } from 'react'
import MediaPlaylist from './components/MediaPlaylist'
import TicketCalling from './components/TicketCalling'
import QueueList from './components/QueueList'

const MERCURE_URL = window.location.origin + '/.well-known/mercure'
const API_BASE = window.location.origin

const DEFAULT_THEME = {
  primary: '#003580', accent: '#007A3D', alert: '#C8102E',
  titulo: 'Sistema de Atendimento — NovoSGA',
  subtitulo: 'Painel de Atendimento',
  logo_totem: null, logo_fivecom: null,
  som_chamada: null,
  tts_provider: 'browser',
}

function hexToRgb(hex) {
  if (!hex || typeof hex !== 'string') return '0 53 128'
  const h = hex.replace('#', '')
  if (h.length < 6) return '0 53 128'
  return [
    parseInt(h.slice(0, 2), 16),
    parseInt(h.slice(2, 4), 16),
    parseInt(h.slice(4, 6), 16)
  ].join(' ')
}

function useTheme(unidadeId) {
  const [theme, setTheme] = useState(DEFAULT_THEME)
  const [tts, setTts] = useState({ provider: 'browser', rate: 1.0, pitch: 1.0, vol_audio: 1.0, vol_video: 1.0 })

  const reloadConfig = useCallback(() => {
    if (!unidadeId) return
    const themeUrl = `/media/config/theme_${unidadeId}.json?_=${Date.now()}`

    fetch(themeUrl)
      .then(r => r.ok ? r.json() : fetch('/media/config/theme.json?_=' + Date.now()).then(res => res.json()))
      .then(t => {
        const merged = { ...DEFAULT_THEME, ...t }
        setTheme(merged)

        // Dynamic Tab Title
        const tabTitle = merged.browser_title || 
          (merged.titulo ? (merged.titulo + (merged.subtitulo ? ' — ' + merged.subtitulo : '')) : 'Painel de Senhas')
        document.title = tabTitle

        // Dynamic Favicon
        const favIcon = merged.favicon || merged.logo_totem || merged.logo_fivecom
        if (favIcon) {
          let link = document.querySelector("link[rel~='icon']")
          if (!link) {
            link = document.createElement('link')
            link.rel = 'icon'
            document.head.appendChild(link)
          }
          link.href = '/media/config/' + favIcon + '?v=' + (merged.updated_at || '1')
        }

        const root = document.documentElement
        if (merged.primary) root.style.setProperty('--pmi-blue', hexToRgb(merged.primary))
        if (merged.accent)  root.style.setProperty('--pmi-green', hexToRgb(merged.accent))
        if (merged.alert)   root.style.setProperty('--pmi-red', hexToRgb(merged.alert))
        document.body.style.backgroundColor = merged.primary
      }).catch(e => console.error(e))
      
    const ttsUrl = `/media/config/tts_${unidadeId}.json?_=${Date.now()}`
    fetch(ttsUrl)
      .then(r => r.ok ? r.json() : fetch('/media/config/tts.json?_=' + Date.now()).then(res => res.json()))
      .then(data => {
        setTts(prev => ({ ...prev, ...data }))
      }).catch(e => console.error(e))
  }, [unidadeId])

  useEffect(() => {
    if (unidadeId) reloadConfig()
  }, [unidadeId, reloadConfig])

  return { theme, tts, reloadConfig }
}

function numToWords(n) {
  const w = ['zero','um','dois','tres','quatro','cinco','seis','sete','oito','nove','dez',
             'onze','doze','treze','quatorze','quinze','dezesseis','dezessete','dezoito','dezenove','vinte']
  return n <= 20 ? (w[n] || String(n)) : String(n)
}

async function speakText(text, tts) {
  const vVoice = (tts.volume_voice !== undefined ? parseFloat(tts.volume_voice) : (tts.vol_audio !== undefined ? parseFloat(tts.vol_audio) : 1.0));
  const finalVol = isNaN(vVoice) ? 1.0 : Math.min(1.0, Math.max(0.0, vVoice));

  if ((tts.enabled === true || (tts.provider && tts.provider !== 'browser'))) {
    try {
      const voiceParam = tts.voice ? ('&voice=' + encodeURIComponent(tts.voice)) : '';
      const url = '/api/totem/tts.php?texto=' + encodeURIComponent(text) + voiceParam + '&_=' + Date.now()
      const res = await fetch(url)
      if (res.ok && res.status === 200) {
        const blob = await res.blob()
        const audioUrl = URL.createObjectURL(blob)
        const audio = new Audio(audioUrl)
        audio.volume = finalVol;
        await new Promise(resolve => { audio.onended = resolve; audio.play().catch(() => resolve()) })
        URL.revokeObjectURL(audioUrl)
        return
      }
    } catch {}
  }
  if (!('speechSynthesis' in window)) return;
  try {
    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'pt-BR';
    utterance.rate = parseFloat(tts.speech_rate || tts.rate) || 1.0;
    utterance.pitch = parseFloat(tts.pitch) || 1.0;
    utterance.volume = finalVol;

    let hasSpoken = false;

    const applyVoiceAndSpeak = () => {
      if (hasSpoken) return;
      hasSpoken = true;
      window.speechSynthesis.onvoiceschanged = null;

      const voices = window.speechSynthesis.getVoices();
      const ptBr = voices.filter(v => v.lang.startsWith('pt') && v.lang.includes('BR'));
      if (ptBr.length) {
        let chosen = null;
        const pref = (tts.voice || '').toLowerCase();

        // 1. Casamento direto pelo nome exato no sistema
        chosen = ptBr.find(v => v.name.toLowerCase().includes(pref) || v.voiceURI.toLowerCase().includes(pref));

        // 2. Se optou por Masculina ('wavenet-b', 'male', 'masculin') -> Procura Antonio, Felipe, Daniel ou a 2a voz do sistema
        if (!chosen && (/wavenet-b|male|masc|homem/i.test(pref))) {
          chosen = ptBr.find(v => /antonio|felipe|daniel|ricardo|male|masc/i.test(v.name)) ||
                   (ptBr.length > 1 ? ptBr[1] : ptBr[0]);
        }
        // 3. Se optou por Feminina ('wavenet-a', 'wavenet-c', 'standard', 'female')
        if (!chosen && (/wavenet-a|wavenet-c|standard-a|female|fem|mulher/i.test(pref))) {
          chosen = ptBr.find(v => /maria|francisca|luciana|vitoria|helen|female|fem/i.test(v.name)) ||
                   ptBr[0];
        }
        const best = chosen || ptBr.find(v => /google|natural|neural|enhanced|luciana|felipe/i.test(v.name)) || ptBr[0];
        utterance.voice = best;
      }
      window.speechSynthesis.speak(utterance);
    };

    if (window.speechSynthesis.getVoices().length === 0) {
      window.speechSynthesis.onvoiceschanged = applyVoiceAndSpeak;
      setTimeout(applyVoiceAndSpeak, 250);
    } else {
      applyVoiceAndSpeak();
    }
  } catch {}
}

export default function App() {
  const [unidadeId, setUnidadeId] = useState(null)
  const [audioUnlocked, setAudioUnlocked] = useState(() => {
    return localStorage.getItem('pmi_audio_unlocked') === 'true'
  })

  useEffect(() => {
    const testAudio = new Audio('data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA');
    testAudio.volume = 0.01;
    testAudio.play().then(() => {
      setAudioUnlocked(true);
      localStorage.setItem('pmi_audio_unlocked', 'true');
    }).catch(() => {
      setAudioUnlocked(false);
    });

    const unlockOnGesture = () => {
      setAudioUnlocked(true);
      localStorage.setItem('pmi_audio_unlocked', 'true');
    };
    window.addEventListener('click', unlockOnGesture, { once: true });
    window.addEventListener('keydown', unlockOnGesture, { once: true });
    window.addEventListener('touchstart', unlockOnGesture, { once: true });
    return () => {
      window.removeEventListener('click', unlockOnGesture);
      window.removeEventListener('keydown', unlockOnGesture);
      window.removeEventListener('touchstart', unlockOnGesture);
    };
  }, []);
  const [unitError, setUnitError] = useState(null)
  const [loadingUnit, setLoadingUnit] = useState(true)

  const [callQueue, setCallQueue] = useState([])
  const [calling, setCalling] = useState(null)
  const [history, setHistory] = useState([])
  const [clock, setClock] = useState(new Date())
  const [mediaVersion, setMediaVersion] = useState(0)

  const { theme, tts, reloadConfig } = useTheme(unidadeId)

  const seenIdsRef = useRef(new Set())
  const isFirstFetchRef = useRef(true)
  const esRef = useRef(null)
  const pendingVoiceRef = useRef(null)

  // Resolução e Validação Dinâmica da Unidade
  useEffect(() => {
    async function initUnit() {
      try {
        const res = await fetch('/api/totem/unidades')
        if (!res.ok) throw new Error('Falha ao carregar lista de unidades')
        const unidadesList = await res.json()

        const params = new URLSearchParams(window.location.search)
        let matchedId = null
        let requestedSlug = ''

        if (params.has('unidade')) {
          const val = parseInt(params.get('unidade'), 10)
          const found = unidadesList.find(u => u.id === val)
          if (found) matchedId = found.id
        }

        if (!matchedId) {
          const parts = window.location.pathname.split('/').filter(Boolean)
          if (parts.length >= 2 && parts[0].toLowerCase() !== 'painel' && parts[0].toLowerCase() !== 'totem') {
            requestedSlug = parts[0].toLowerCase()
            const found = unidadesList.find(u => 
              u.slug === requestedSlug || (u.aliases && u.aliases.includes(requestedSlug))
            )
            if (found) {
              matchedId = found.id
            } else {
              setUnitError(`A unidade "${requestedSlug}" não existe ou está desativada no sistema.`)
              setLoadingUnit(false)
              return
            }
          }
        }

        if (!matchedId) {
          matchedId = unidadesList.length > 0 ? unidadesList[0].id : 1
        }

        setUnidadeId(matchedId)
        setLoadingUnit(false)
      } catch (err) {
        setUnidadeId(1)
        setLoadingUnit(false)
      }
    }
    initUnit()
  }, [])

  useEffect(() => {
    if (!calling && callQueue.length > 0) {
      const [next, ...rest] = callQueue
      setCalling(next)
      setCallQueue(rest)
      const rawNome = next.nomeCliente || ('Senha ' + next.senha);
      const parts = rawNome.trim().split(/\s+/);
      const nomeSpoken = rawNome.trim();
      const guicheNum = next.numeroLocal > 0 ? numToWords(next.numeroLocal) : '';
      const localName = next.local || 'guichê';
      
      if (tts && tts.template && tts.template.trim() !== '') {
        let customText = tts.template
          .replace(/\{nome\}/gi, nomeSpoken)
          .replace(/\{senha\}/gi, next.senha || '')
          .replace(/\{local\}/gi, localName)
          .replace(/\{numero\}/gi, guicheNum);
        if (next.peso > 0 && !customText.toLowerCase().includes('priorit')) {
          customText += '. Atendimento prioritário.';
        }
        pendingVoiceRef.current = customText;
      } else {
        const guiche = next.numeroLocal > 0 ? (', dirija-se ao guichê ' + guicheNum) : '';
        const prioridade = next.peso > 0 ? '. Atendimento prioritário.' : '';
        pendingVoiceRef.current = 'Atenção, ' + nomeSpoken + guiche + prioridade;
      }
    }
  }, [calling, callQueue, tts])

  const fetchQueue = useCallback(async () => {
    if (!unidadeId) return
    try {
      const params = new URLSearchParams(window.location.search)
      const servicos = params.get('servicos') || ''
      const qs = servicos ? ('?servicos=' + servicos) : ''
      const res = await fetch(API_BASE + '/api/unidades/' + unidadeId + '/painel' + qs)
      if (!res.ok) return
      const data = await res.json()

      if (isFirstFetchRef.current) {
        isFirstFetchRef.current = false
        data.forEach(t => seenIdsRef.current.add(t.id))
        setHistory(data.slice(0, 20))
        return
      }

      const novos = data.filter(t => !seenIdsRef.current.has(t.id))
      if (novos.length === 0) return
      novos.forEach(t => seenIdsRef.current.add(t.id))

      setHistory(prev => {
        const merged = [...novos, ...prev]
        const seen = new Set()
        return merged.filter(t => {
          if (seen.has(t.senha)) return false
          seen.add(t.senha)
          return true
        }).slice(0, 20)
      })

      setCallQueue(prev => [...prev, ...novos])
    } catch {
      // network error
    }
  }, [unidadeId])

  const handleSoundEnd = useCallback(() => {
    if (pendingVoiceRef.current) {
      speakText(pendingVoiceRef.current, tts)
      pendingVoiceRef.current = null
    }
  }, [tts])

  const connectSSE = useCallback(() => {
    if (!unidadeId) return
    if (esRef.current) esRef.current.close()
    const url = new URL(MERCURE_URL)
    url.searchParams.append('topic', '/paineis')
    url.searchParams.append('topic', '/unidades/' + unidadeId + '/painel')
    const es = new EventSource(url.toString())
    esRef.current = es
    es.onmessage = (e) => {
      try {
        const msg = JSON.parse(e.data);
        if (msg.action === 'reload_config' || msg.action === 'reload_media') {
          reloadConfig();
          setMediaVersion(v => v + 1);
          return;
        } else if (msg.action === 'reload_window') {
          window.location.reload();
          return;
        } else if (msg.action === 'test_audio') {
          const url = ((tts.chime && tts.chime !== 'none') ? ('/media/config/' + tts.chime) : (tts.som_chamada ? ('/media/config/' + tts.som_chamada) : '/bundles/novosgaattendance/sounds/alert.wav'));
          const audio = new Audio(url + '?_=' + Date.now());
          const vol = (tts.volume_chime !== undefined ? parseFloat(tts.volume_chime) : (tts.vol_chime !== undefined ? parseFloat(tts.vol_chime) : 1.0));
          audio.volume = isNaN(vol) ? 1.0 : Math.min(1.0, Math.max(0.0, vol));
          audio.play().catch(() => {});
          speakText("Áudio da TV ativado com sucesso", tts);
          return;
        }
      } catch (err) {}
      fetchQueue()
    }

    es.onerror = () => { es.close(); setTimeout(connectSSE, 4000) }
  }, [unidadeId, fetchQueue, reloadConfig])

  useEffect(() => {
    if (!unidadeId) return
    fetchQueue()
    connectSSE()
    const clockTimer = setInterval(() => setClock(new Date()), 1000)
    const pollTimer = setInterval(fetchQueue, 30000)
    return () => {
      clearInterval(clockTimer)
      clearInterval(pollTimer)
      if (esRef.current) esRef.current.close()
    }
  }, [unidadeId, fetchQueue, connectSSE])

  const dismissCalling = useCallback(() => {
    setCalling(null)
    pendingVoiceRef.current = null
  }, [])

  const handleUnlockAudio = (forceTest = false) => {
    if (!forceTest && audioUnlocked) return;
    setAudioUnlocked(true);
    localStorage.setItem('pmi_audio_unlocked', 'true');
    const url = ((tts.chime && tts.chime !== 'none') ? ('/media/config/' + tts.chime) : (tts.som_chamada ? ('/media/config/' + tts.som_chamada) : '/bundles/novosgaattendance/sounds/alert.wav'));
    const audio = new Audio(url + '?_=' + Date.now());
    const vol = (tts.volume_chime !== undefined ? parseFloat(tts.volume_chime) : (tts.vol_chime !== undefined ? parseFloat(tts.vol_chime) : 1.0));
    audio.volume = isNaN(vol) ? 1.0 : Math.min(1.0, Math.max(0.0, vol));
    audio.play().catch(() => {
      const fb = new Audio('/bundles/novosgaattendance/sounds/alert.wav');
      fb.volume = audio.volume;
      fb.play().catch(() => {});
    });
    speakText("Áudio da TV ativado com sucesso", tts);
  };

  if (loadingUnit) {
    return (
      <div className="w-screen h-screen flex flex-col items-center justify-center bg-slate-900 text-white">
        <div className="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
        <p className="text-lg font-semibold">Carregando painel de atendimento...</p>
      </div>
    )
  }

  if (unitError) {
    return (
      <div className="w-screen h-screen flex flex-col items-center justify-center p-8 bg-red-950 text-white text-center">
        <div className="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mb-6">
          <svg className="w-10 h-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h1 className="text-3xl font-black mb-2">Unidade Não Encontrada</h1>
        <p className="text-xl text-red-200/90 max-w-xl mb-8">{unitError}</p>
        <button
          onClick={() => window.location.href = '/'}
          className="px-6 py-3 bg-white text-red-950 rounded-xl font-bold hover:bg-red-100 transition-colors shadow-lg"
        >
          Voltar à página inicial
        </button>
      </div>
    )
  }

  const timeStr = clock.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
  const dateStr = clock.toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long' })

  return (
    <div 
      onClick={handleUnlockAudio}
      className="w-screen h-screen flex flex-col overflow-hidden bg-gradient-to-br from-pmi-blue via-pmi-blue/95 to-slate-950 relative"
    >
      {!audioUnlocked && (
        <div 
          onClick={(e) => { e.stopPropagation(); handleUnlockAudio(); }}
          className="absolute top-4 left-1/2 -translate-x-1/2 z-50 bg-slate-900/95 hover:bg-slate-800 text-white px-6 py-3 rounded-full shadow-2xl border border-white/20 flex items-center gap-3 cursor-pointer animate-bounce transition-all transform hover:scale-105"
        >
          <span className="text-2xl">🔊</span>
          <div>
            <p className="text-sm font-extrabold leading-tight text-white">Clique na tela para ativar o som</p>
            <p className="text-[11px] text-emerald-400 font-semibold mt-0.5">Um clique libera chamadas automáticas de áudio</p>
          </div>
        </div>
      )}
      <header className="flex-none bg-pmi-blue border-b border-white/10 px-6 py-3 flex items-center justify-between">
        <div className="flex items-center gap-4">
          {theme.logo_totem ? (
            <img
              src={'/media/config/' + theme.logo_totem + '?v=' + (theme.updated_at || '1')}
              style={{ height: 40, objectFit: 'contain' }}
              alt="logo"
            />
          ) : (
            <div className="w-10 h-10 bg-pmi-green rounded-lg flex items-center justify-center font-black text-white text-sm">
              SGA
            </div>
          )}
          <div>
            <p className="text-white font-bold text-base leading-tight">{theme.titulo || 'Sistema de Atendimento — NovoSGA'}</p>
            <p className="text-white/50 text-xs">{theme.subtitulo ? (theme.subtitulo + ' — ') : ''}Painel de Atendimento</p>
          </div>
        </div>
        <div className="text-right">
          <p className="text-white font-black text-2xl tabular-nums">{timeStr}</p>
          <p className="text-white/50 text-xs capitalize">{dateStr}</p>
        </div>
      </header>

      <div className="flex-1 flex min-h-0">
        <div className="flex-1 min-w-0">
          <MediaPlaylist unidadeId={unidadeId} muted={!!calling} volume={tts.vol_video} />
        </div>
        <aside className="w-[32rem] flex-none bg-pmi-blue/95 border-l border-white/10 flex flex-col">
          <QueueList
            current={history[0] || null}
            previous={history.slice(1)}
            queueLength={callQueue.length}
            theme={theme} tts={tts} reloadKey={mediaVersion}
          />
        </aside>
      </div>

      {calling && (
        <TicketCalling
          ticket={calling}
          onDismiss={dismissCalling}
          onSoundEnd={handleSoundEnd}
          onAutoplayBlocked={() => {}}
          theme={theme} tts={tts} reloadKey={mediaVersion}
          somUrl={((tts.chime && tts.chime !== 'none') ? ('/media/config/' + tts.chime) : (tts.som_chamada ? ('/media/config/' + tts.som_chamada) : null))}
        />
      )}
    </div>
  )
}
