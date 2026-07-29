import { useEffect, useState } from 'react'



function playCallSound(volume = 1.0) {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)()
    const beep = (freq, startAt, dur) => {
      const osc = ctx.createOscillator()
      const gain = ctx.createGain()
      osc.connect(gain)
      gain.connect(ctx.destination)
      osc.type = 'sine'
      osc.frequency.value = freq
      gain.gain.setValueAtTime(0, ctx.currentTime + startAt)
      gain.gain.linearRampToValueAtTime(0.7 * volume, ctx.currentTime + startAt + 0.015)
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + startAt + dur)
      osc.start(ctx.currentTime + startAt)
      osc.stop(ctx.currentTime + startAt + dur + 0.05)
    }
    beep(880,  0.00, 0.18)
    beep(1046, 0.22, 0.18)
    beep(880,  0.44, 0.38)
    return 950
  } catch {
    return 0
  }
}

const FLASH_DURATION = 3600
const TOTAL_DURATION = 9000

export default function TicketCalling({ ticket, onDismiss, onSoundEnd, theme, somUrl, tts, onAutoplayBlocked }) {
  const [phase, setPhase] = useState(0)
  const [flashing, setFlashing] = useState(true)

  const COLORS = [
    (theme?.blink_primary !== false) ? { bg: theme?.primary || '#003580', text: '#FFFFFF', sub: 'rgba(255,255,255,0.70)' } : null,
    (theme?.blink_accent !== false) ? { bg: theme?.accent || '#007A3D', text: '#FFFFFF', sub: 'rgba(255,255,255,0.70)' } : null,
    (theme?.blink_alert !== false) ? { bg: theme?.alert || '#C8102E', text: '#FFFFFF', sub: 'rgba(255,255,255,0.70)' } : null,
  ].filter(Boolean)

  if (COLORS.length === 0) {
    COLORS.push({ bg: theme?.primary || '#003580', text: '#FFFFFF', sub: 'rgba(255,255,255,0.70)' })
  }

  useEffect(() => {
    if (!ticket) return

    setPhase(0)
    setFlashing(true)

    let soundDelay = 1500;
    const cVol = (tts && (tts.volume_chime !== undefined ? parseFloat(tts.volume_chime) : (tts.vol_chime !== undefined ? parseFloat(tts.vol_chime) : (tts.vol_audio !== undefined ? parseFloat(tts.vol_audio) : 1.0))))
    const finalVol = isNaN(cVol) ? 1.0 : Math.min(1.0, Math.max(0.0, cVol));

    const playAudioWithFallback = (url) => {
      const audio = new Audio(url);
      audio.volume = finalVol;
      audio.play().catch((err) => {
        const fb = new Audio('/bundles/novosgaattendance/sounds/alert.wav');
        fb.volume = finalVol;
        fb.play().catch(() => {
          playCallSound(finalVol);
        });
      });
    };

    if (somUrl) {
      playAudioWithFallback(somUrl + '?_=' + Date.now());
    } else {
      playAudioWithFallback('/bundles/novosgaattendance/sounds/alert.wav');
    }

    const soundTimer = onSoundEnd ? setTimeout(onSoundEnd, soundDelay) : null

    let idx = 0
    const interval = setInterval(() => {
      idx = (idx + 1) % COLORS.length
      setPhase(idx)
    }, 350)

    const stopFlash = setTimeout(() => {
      clearInterval(interval)
      setFlashing(false)
      setPhase(0)
    }, FLASH_DURATION)

    const textLen = (ticket.nomeCliente || ticket.senha || '').length;
    const dynamicDuration = Math.max(9500, 3500 + (textLen * 180));

    const dismiss = setTimeout(onDismiss, dynamicDuration)

    return () => {
      clearInterval(interval)
      clearTimeout(stopFlash)
      clearTimeout(dismiss)
      if (soundTimer) clearTimeout(soundTimer)
    }
  }, [ticket?.id, onDismiss, onSoundEnd, somUrl])

  if (!ticket) return null

  const color = flashing ? COLORS[phase] : { bg: theme?.primary || '#003580', text: '#FFFFFF', sub: 'rgba(255,255,255,0.62)' }

  return (
    <div style={{
      position: 'fixed', inset: 0, zIndex: 50,
      display: 'flex', flexDirection: 'column',
      alignItems: 'center', justifyContent: 'center',
      background: color.bg,
      transition: flashing ? 'none' : 'background 0.5s ease',
    }}>

      {/* Faixa superior */}
      <div style={{
        position: 'absolute', top: 0, left: 0, right: 0,
        padding: '10px 32px',
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        borderBottom: '3px solid ' + color.text,
      }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
          {theme?.logo_totem && (
            <img
              src={'/media/config/' + theme.logo_totem + '?v=' + (theme.updated_at || '1')}
              style={{ height: 36, objectFit: 'contain' }}
              alt="logo"
            />
          )}
          <span style={{ color: color.text, fontWeight: 700, fontSize: '1.1rem', letterSpacing: '0.04em' }}>
            {theme?.titulo || 'Sistema de Atendimento — NovoSGA'}
          </span>
        </div>
        <span style={{ color: color.sub, fontWeight: 500, fontSize: '1rem' }}>
          {theme?.subtitulo || ''}
        </span>
      </div>

      {/* Rotulo */}
      <p style={{
        color: color.sub,
        fontSize: 'clamp(1rem, 2.5vw, 1.8rem)',
        fontWeight: 600,
        textTransform: 'uppercase',
        letterSpacing: '0.25em',
        marginBottom: '0.3em',
      }}>
        {ticket.nomeCliente ? 'Atenção' : 'Chamando senha'}
      </p>

      {/* Nome como elemento central */}
      <div style={{
        fontSize: 'clamp(3.5rem, 12vw, 10rem)',
        fontWeight: 900, color: color.text, lineHeight: 1.05,
        letterSpacing: '-0.01em',
        textShadow: '0 4px 40px rgba(0,0,0,0.25)',
        textAlign: 'center',
        textTransform: 'uppercase',
        maxWidth: '90vw', wordBreak: 'break-word',
      }}>
        {ticket.nomeCliente || ticket.senha}
      </div>

      {/* Numero da senha — referencia pequena */}
      {ticket.nomeCliente && (
        <p style={{ color: color.sub, fontSize: 'clamp(1rem,2vw,1.5rem)', fontWeight: 600, marginTop: '0.3em', opacity: 0.7 }}>
          Senha: {ticket.senha}
        </p>
      )}

      {/* Guiche / local */}
      {(ticket.local || ticket.numeroLocal > 0) && (
        <div style={{
          marginTop: '2rem',
          border: '3px solid ' + color.text,
          borderRadius: 16,
          padding: 'clamp(10px,2vw,18px) clamp(24px,5vw,60px)',
          display: 'flex', flexDirection: 'column', alignItems: 'center',
        }}>
          <p style={{ color: color.sub, fontSize: 'clamp(0.7rem,1.2vw,0.95rem)', textTransform: 'uppercase', letterSpacing: '0.2em', marginBottom: 4 }}>
            Dirija-se ao
          </p>
          <p style={{ color: color.text, fontSize: 'clamp(1.5rem,5vw,4rem)', fontWeight: 800 }}>
            {ticket.local}{ticket.numeroLocal > 0 ? (' ' + ticket.numeroLocal) : ''}
          </p>
        </div>
      )}

      {/* Servico */}
      {ticket.servico?.nome && (
        <p style={{ color: color.sub, fontSize: 'clamp(0.9rem,2vw,1.4rem)', fontWeight: 500, marginTop: '1.2rem' }}>
          {ticket.servico.nome}
        </p>
      )}

      {/* Badge prioridade */}
      {ticket.peso > 0 && (
        <span style={{
          marginTop: '1rem',
          background: 'rgba(255,255,255,0.2)',
          color: color.text,
          padding: '4px 20px', borderRadius: 9999,
          fontWeight: 700, fontSize: '1.1rem',
          textTransform: 'uppercase', letterSpacing: '0.1em',
          border: '1px solid ' + color.text,
        }}>
          Prioritário
        </span>
      )}

      {/* Barra de progresso */}
      <div style={{
        position: 'absolute', bottom: 0, left: 0,
        height: 6,
        background: color.text,
        width: '100%',
        animation: 'shrinkProgress ' + TOTAL_DURATION + 'ms linear forwards',
        opacity: 0.7,
      }} />

      <style>{`
        @keyframes shrinkProgress { from { width: 100% } to { width: 0% } }
      `}</style>
    </div>
  )
}
