export default function QueueList({ current, previous, queueLength, theme }) {
  return (
    <div className="flex flex-col h-full p-5 gap-4">

      {/* Chamando agora */}
      <div className="flex-none">
        <p className="text-white/40 text-sm uppercase tracking-widest font-semibold mb-3">
          Chamando agora
        </p>
        {current ? (
          <div
            className="rounded-2xl p-5 flex flex-col gap-2"
            style={{
              background: current.corPrioridade || '#007A3D',
              boxShadow: '0 0 28px rgba(0,0,0,0.4)',
            }}
          >
            <span className="font-black text-white leading-tight" style={{ fontSize: 'clamp(1.5rem,3.5vw,2.8rem)', textTransform: 'uppercase' }}>
              {current.nomeCliente || current.senha}
            </span>
            {current.nomeCliente && (
              <span className="text-white/50 text-xs font-mono mt-0.5">Senha: {current.senha}</span>
            )}
            {current.servico?.nome && (
              <span className="text-white/80 text-base font-medium">{current.servico.nome}</span>
            )}
            {(current.local || current.numeroLocal > 0) && (
              <span className="text-white font-bold text-xl mt-1">
                {current.local}{current.numeroLocal > 0 ? (' ' + current.numeroLocal) : ''}
              </span>
            )}
            {current.peso > 0 && (
              <span className="text-white/75 text-sm uppercase tracking-wide font-semibold">
                {current.prioridade}
              </span>
            )}
          </div>
        ) : (
          <div className="rounded-2xl p-5 bg-white/5 text-center">
            <p className="text-white/30 text-base">Nenhuma senha chamada ainda</p>
          </div>
        )}
        {queueLength > 0 && (
          <p className="text-yellow-300/80 text-sm mt-2 text-center font-medium animate-pulse">
            +{queueLength} na fila aguardando...
          </p>
        )}
      </div>

      <div className="border-t border-white/10 flex-none" />

      {/* Chamadas anteriores */}
      <div className="flex-none">
        <p className="text-white/40 text-sm uppercase tracking-widest font-semibold mb-2">
          Chamadas anteriores
        </p>
      </div>

      <div className="flex-1 min-h-0 overflow-y-auto flex flex-col gap-3 pr-1">
        {previous.length === 0 ? (
          <p className="text-white/20 text-base text-center py-4">Sem historico</p>
        ) : (
          previous.map((t, i) => (
            <div
              key={t.id}
              className="rounded-xl px-4 py-3 flex items-center justify-between"
              style={{ background: 'rgba(255,255,255,0.07)', opacity: Math.max(0.35, 1 - i * 0.08) }}
            >
              <div className="flex flex-col gap-0.5">
                <span className="text-white font-bold leading-tight" style={{ fontSize: '1.1rem', textTransform: 'uppercase' }}>
                  {t.nomeCliente || t.senha}
                </span>
                {t.nomeCliente && <span className="text-white/40 text-xs font-mono">{t.senha}</span>}
                <span className="text-white/50 text-sm">{t.servico?.nome}</span>
              </div>
              <div className="text-right flex flex-col items-end gap-1">
                <span className="text-white/70 text-base font-bold">
                  {t.local}{t.numeroLocal > 0 ? (' ' + t.numeroLocal) : ''}
                </span>
                {t.peso > 0 && (
                  <span
                    className="text-sm px-3 py-0.5 rounded-full text-white font-bold"
                    style={{ background: t.corPrioridade || '#C8102E' }}
                  >
                    Prioritario
                  </span>
                )}
              </div>
            </div>
          ))
        )}
      </div>

      <div className="flex-none flex items-center gap-2 text-white/30 text-sm pt-2 border-t border-white/10">
        <span className="w-2 h-2 rounded-full bg-pmi-green animate-pulse" />
        Conectado em tempo real
      </div>
    </div>
  )
}
