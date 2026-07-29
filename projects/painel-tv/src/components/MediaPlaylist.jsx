import { useState, useEffect, useRef, useCallback } from "react"

const SLIDE_DURATION = 8000

export default function MediaPlaylist({ unidadeId = 1, muted = false, volume = 1.0, reloadKey = 0 }) {
  const [media, setMedia] = useState([])
  const [current, setCurrent] = useState(0)
  const [playKey, setPlayKey] = useState(0)
  const timerRef = useRef(null)
  const videoRef = useRef(null)
  const mutedRef = useRef(muted)

  useEffect(() => {
    mutedRef.current = muted
    if (videoRef.current) {
      videoRef.current.muted = muted
      videoRef.current.volume = volume
      if (!muted) {
        videoRef.current.play().catch(() => {
          videoRef.current.muted = true
          videoRef.current.play()
        })
      }
    }
  }, [muted, volume])

  const fetchMedia = useCallback(async () => {
    try {
      const res = await fetch("/api/totem/midia.php?unidade_id=" + (unidadeId || 1) + "&_=" + Date.now())
      if (res.ok) {
        const items = await res.json()
        if (Array.isArray(items) && items.length > 0) {
          setMedia(items.map(m => ({ type: m.type, src: m.url })))
        } else {
          setMedia([])
        }
      }
    } catch {}
  }, [unidadeId])

  const advance = useCallback(() => {
    setCurrent(c => (c + 1) % (media.length || 1))
    setPlayKey(k => k + 1)
  }, [media.length])

  useEffect(() => {
    fetchMedia()
    const interval = setInterval(fetchMedia, 30000)
    return () => clearInterval(interval)
  }, [fetchMedia, reloadKey])

  useEffect(() => {
    if (media.length === 0) return
    const item = media[current]
    if (item && item.type === "image") {
      timerRef.current = setTimeout(advance, SLIDE_DURATION)
    }
    return () => clearTimeout(timerRef.current)
  }, [current, playKey, media, advance])

  if (media.length === 0) {
    return (
      <div className="w-full h-full flex flex-col items-center justify-center bg-pmi-blue">
        <div className="text-center opacity-30">
          <div className="text-6xl font-black text-white mb-4">SGA</div>
          <p className="text-white text-lg font-medium">Sistema de Atendimento — NovoSGA</p>
          <p className="text-white/60 text-sm mt-2">Painel de Atendimento</p>
        </div>
      </div>
    )
  }

  const item = media[current]
  const onlyOneVideo = media.length === 1 && item.type === "video"

  const bgStyle = {
    position: "absolute", inset: 0,
    width: "100%", height: "100%",
    objectFit: "cover",
    transform: "scale(1.12)",
    filter: "blur(48px) brightness(0.65) saturate(1.5)",
    pointerEvents: "none",
  }

  const fgStyle = {
    position: "absolute", inset: 0,
    width: "100%", height: "100%",
    objectFit: "contain",
    filter: "drop-shadow(0 6px 28px rgba(0,0,0,0.5))",
  }

  return (
    <div style={{ width: "100%", height: "100%", position: "relative", overflow: "hidden", background: "#000" }}>
      {item.type === "image" ? (
        <img key={"bg-" + playKey} src={item.src} alt="" aria-hidden="true" style={bgStyle} />
      ) : (
        <video key={"bg-vid-" + playKey} src={item.src} aria-hidden="true" style={bgStyle} autoPlay muted playsInline loop />
      )}

      <div style={{
        position: "absolute", inset: 0,
        background: "rgba(0,0,0,0.15)",
        backdropFilter: "blur(4px) brightness(0.9)",
      }} />

      {item.type === "image" ? (
        <img key={"img-" + playKey} src={item.src} alt="" style={fgStyle} />
      ) : (
        <video
          key={"vid-" + playKey}
          ref={videoRef}
          src={item.src}
          style={fgStyle}
          autoPlay
          playsInline
          loop={onlyOneVideo}
          onEnded={onlyOneVideo ? undefined : advance}
          onCanPlay={e => {
            e.target.muted = mutedRef.current; e.target.volume = volume;
            if (!mutedRef.current) {
              e.target.play().catch(() => {
                e.target.muted = true
                e.target.play()
              })
            }
          }}
        />
      )}

      {media.length > 1 && (
        <div style={{
          position: "absolute", bottom: 16, left: 0, right: 0,
          display: "flex", justifyCenter: "center", gap: 6,
          zIndex: 10,
        }}>
          {media.map((_, i) => (
            <div key={i} style={{
              height: 4,
              width: i === current ? 22 : 6,
              borderRadius: 9999,
              background: i === current ? "rgba(255,255,255,0.92)" : "rgba(255,255,255,0.35)",
              boxShadow: i === current ? "0 0 8px rgba(255,255,255,0.55)" : "none",
              transition: "all 0.35s ease",
            }} />
          ))}
        </div>
      )}
    </div>
  )
}
