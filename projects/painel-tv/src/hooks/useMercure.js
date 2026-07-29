import { useEffect, useRef, useCallback } from 'react'

export function useMercure(mercureUrl, topics, onMessage) {
  const esRef = useRef(null)
  const onMessageRef = useRef(onMessage)
  onMessageRef.current = onMessage

  const connect = useCallback(() => {
    if (esRef.current) {
      esRef.current.close()
    }

    const url = new URL(mercureUrl)
    topics.forEach(t => url.searchParams.append('topic', t))

    const es = new EventSource(url.toString())
    esRef.current = es

    es.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data)
        onMessageRef.current(data)
      } catch (e) {
        // ignore parse errors
      }
    }

    es.onerror = () => {
      // Reconnect after 3s on error
      es.close()
      setTimeout(connect, 3000)
    }
  }, [mercureUrl, topics.join(',')])

  useEffect(() => {
    connect()
    return () => {
      if (esRef.current) {
        esRef.current.close()
      }
    }
  }, [connect])
}
