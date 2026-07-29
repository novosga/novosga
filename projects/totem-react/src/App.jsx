import { useState, useEffect, useCallback, useRef } from 'react'

const PRINTER_URL = 'http://localhost:3001'
const BASE_URL    = window.location.origin
const IDLE_TIMEOUT = 45000
const MERCURE_URL = window.location.origin + '/.well-known/mercure';

// ---------- Algoritmo Oficial de Validação do Dígito Verificador do CPF ----------
function isValidCPF(cpf) {
  const str = cpf.replace(/\D/g, '')
  if (str.length !== 11 || /^(\d)\1{10}$/.test(str)) return false
  let sum = 0, rest
  for (let i = 1; i <= 9; i++) sum += parseInt(str.substring(i - 1, i)) * (11 - i)
  rest = (sum * 10) % 11
  if (rest === 10 || rest === 11) rest = 0
  if (rest !== parseInt(str.substring(9, 10))) return false
  sum = 0
  for (let i = 1; i <= 10; i++) sum += parseInt(str.substring(i - 1, i)) * (12 - i)
  rest = (sum * 10) % 11
  if (rest === 10 || rest === 11) rest = 0
  return rest === parseInt(str.substring(10, 11))
}

function maskCPF(v) {
  const d = v.replace(/\D/g, '').slice(0, 11)
  return d.replace(/(\d{3})(\d)/, '$1.$2')
           .replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
           .replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4')
}

// ---------- Auth ----------
let authToken = null
let tokenExpiry = 0

async function getToken() {
  if (authToken && Date.now() < tokenExpiry - 30000) return authToken
  const res = await fetch(`${BASE_URL}/api/totem/token.php`)
  if (!res.ok) throw new Error('Auth failed')
  const data = await res.json()
  authToken  = data.access_token
  tokenExpiry = Date.now() + (data.expires_in || 3600) * 1000
  return authToken
}

async function apiFetch(path, opts = {}) {
  const token = await getToken()
  return fetch(`${BASE_URL}${path}`, {
    ...opts,
    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', ...opts.headers },
  })
}

// ---------- Theming ----------
function hexToRgb(hex) {
  if (!hex || typeof hex !== 'string') return '0 53 128'
  const h = hex.replace('#', '')
  if (h.length < 6) return '0 53 128'
  return [
    parseInt(h.slice(0, 2), 16),
    parseInt(h.slice(2, 4), 16),
    parseInt(h.slice(4, 6), 16),
  ].join(' ')
}

const DEFAULT_THEME = {
  primary: '#003580', accent: '#007A3D', alert: '#C8102E',
  titulo: 'Sistema de Atendimento — NovoSGA',
  subtitulo: 'Unidade de Atendimento',
  logo_totem: null, logo_fivecom: null,
}

function useTheme(unidadeId) {
  const [theme, setTheme] = useState(DEFAULT_THEME)

  const reloadTheme = useCallback(() => {
    const unitPart = unidadeId ? ('_' + unidadeId) : ''
    const themeUrl = '/media/config/theme' + unitPart + '.json?_=' + Date.now()
    fetch(themeUrl)
      .then(r => r.ok ? r.json() : fetch('/media/config/theme.json?_=' + Date.now()).then(res => res.json()))
      .then(t => {
        const merged = { ...DEFAULT_THEME, ...t }
        setTheme(merged)
        if (merged.logo_fivecom) {
          let link = document.querySelector("link[rel~='icon']");
          if (!link) {
            link = document.createElement('link');
            link.rel = 'icon';
            document.head.appendChild(link);
          }
          link.href = '/media/config/' + merged.logo_fivecom + '?v=' + (merged.updated_at || '1');
        }
        const root = document.documentElement
        if (merged.primary) root.style.setProperty('--pmi-blue',  hexToRgb(merged.primary))
        if (merged.accent)  root.style.setProperty('--pmi-green', hexToRgb(merged.accent))
        if (merged.alert)   root.style.setProperty('--pmi-red',   hexToRgb(merged.alert))
        document.body.style.backgroundColor = merged.primary
      })
      .catch(() => {})
  }, [unidadeId])

  useEffect(() => { reloadTheme() }, [reloadTheme])

  return { theme, reloadTheme }
}

// ---------- Error Screen ----------
export function ErrorScreen({ message, onRetry }) {
  return (
    <div className="h-full flex flex-col items-center justify-center bg-pmi-red text-white p-8 text-center select-none">
      <div className="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center mb-6 shadow-2xl">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" className="w-14 h-14">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
      </div>
      <h2 className="text-white text-3xl font-black mb-3 leading-tight">Atenção</h2>
      <p className="text-white/90 text-xl max-w-lg leading-relaxed mb-10">{message}</p>
      
      <button 
        onClick={onRetry}
        className="px-10 py-4 bg-white text-pmi-red rounded-2xl font-bold text-lg shadow-2xl active:scale-95 transition-transform"
      >
        Voltar ao início
      </button>
    </div>
  )
}

// ---------- Mini Header ----------
function MiniHeader({ theme }) {
  const logoUrl = theme.logo_totem ? `/media/config/${theme.logo_totem}?v=${theme.updated_at || '1'}` : null

  return (
    <div className="flex items-center justify-between px-8 py-4 bg-pmi-blue border-b border-white/10 select-none">
      <div className="flex items-center gap-4">
        {logoUrl ? (
          <img src={logoUrl} alt="Logo" className="h-10 object-contain" />
        ) : (
          <div className="w-10 h-10 rounded-xl bg-pmi-green flex items-center justify-center shadow-md">
            <span className="text-white font-black text-sm">PMI</span>
          </div>
        )}
        <div>
          <h2 className="text-white font-bold text-base leading-tight">{theme.titulo}</h2>
          {theme.subtitulo && <p className="text-white/60 text-xs">{theme.subtitulo}</p>}
        </div>
      </div>
    </div>
  )
}

// ---------- Tela Idle ----------
function IdleScreen({ onStart, theme }) {
  const logoUrl    = theme.logo_totem   ? `/media/config/${theme.logo_totem}?v=${theme.updated_at || '1'}`   : null
  const fivecomUrl = theme.logo_fivecom ? `/media/config/${theme.logo_fivecom}?v=${theme.updated_at || '1'}` : null

  return (
    <div
      className="h-full flex flex-col items-center justify-center bg-gradient-to-br from-pmi-blue via-pmi-blue/90 to-slate-950 cursor-pointer select-none relative"
      onClick={onStart}
    >
      <div className="mb-10 text-center px-8">
        {logoUrl ? (
          <img
            src={logoUrl}
            alt="Logo"
            className="max-h-40 max-w-xs mx-auto mb-6 object-contain drop-shadow-xl animate-fadeIn"
          />
        ) : (
          <div className="w-28 h-28 rounded-3xl bg-pmi-green flex items-center justify-center mx-auto mb-6 shadow-xl">
            <span className="text-white font-black text-4xl tracking-tight">PMI</span>
          </div>
        )}
        <h1 className="text-white text-4xl font-black mb-3 leading-tight" style={{ textShadow: '0 2px 16px rgba(0,0,0,.25)' }}>
          {theme.titulo}
        </h1>
        {theme.subtitulo && (
          <p className="text-white/60 text-xl font-medium">{theme.subtitulo}</p>
        )}
      </div>

      <div className="bg-white/10 backdrop-blur-sm border border-white/20 rounded-3xl px-14 py-8 text-center animate-pulse shadow-2xl">
        <p className="text-white text-3xl font-bold mb-1">Toque para retirar sua senha</p>
        <p className="text-white/40 text-base mt-2 tracking-wide">Touch to get your ticket</p>
      </div>

      {fivecomUrl && (
        <div className="absolute bottom-7 right-8 flex items-center gap-2 opacity-60 hover:opacity-90 transition-opacity">
          <span className="text-white/40 text-xs uppercase tracking-widest">Powered by</span>
          <img src={fivecomUrl} alt="Fivecom" className="h-7 object-contain" />
        </div>
      )}
    </div>
  )
}

// ---------- Modal de Teclado Touch Inteligente (Context-Aware) ----------
function TouchKeyboardModal({ targetField, initialValue, onConfirm, onClose }) {
  const [val, setVal] = useState(initialValue || '')

  const isNumeric = targetField === 'cpf'
  const keysNum = ['1','2','3','4','5','6','7','8','9','0']
  const keysAlpha = [
    ['Q','W','E','R','T','Y','U','I','O','P'],
    ['A','S','D','F','G','H','J','K','L'],
    ['Z','X','C','V','B','N','M']
  ]

  const handleKeyPress = (k) => {
    if (isNumeric) {
      const digits = val.replace(/\D/g, '')
      if (digits.length < 11) {
        setVal(maskCPF(digits + k))
      }
    } else {
      setVal(v => v + k)
    }
  }

  const handleBackspace = () => {
    if (isNumeric) {
      const digits = val.replace(/\D/g, '').slice(0, -1)
      setVal(maskCPF(digits))
    } else {
      setVal(v => v.slice(0, -1))
    }
  }

  return (
    <div className="fixed inset-0 bg-slate-950/85 backdrop-blur-md z-50 flex flex-col justify-end p-6 select-none animate-fadeIn">
      <div className="bg-slate-900 border border-white/20 rounded-3xl p-6 max-w-3xl mx-auto w-full shadow-2xl">
        <div className="flex items-center justify-between mb-3">
          <span className="text-white/70 font-bold text-sm uppercase tracking-wider">
            ⌨️ Teclado Touch — Campo: <strong className="text-white">{targetField.toUpperCase()}</strong>
          </span>
          <button onClick={onClose} className="text-white/50 hover:text-white font-bold text-xl px-2 py-1">✕</button>
        </div>

        <div className="bg-white/10 border-2 border-pmi-green rounded-2xl p-4 mb-4 text-center font-mono font-bold text-3xl text-white tracking-widest min-h-[64px] flex items-center justify-center">
          {val || <span className="opacity-30">Digite aqui...</span>}
        </div>

        {isNumeric ? (
          <div className="grid grid-cols-5 gap-3 mb-4">
            {keysNum.map(k => (
              <button
                key={k}
                onClick={() => handleKeyPress(k)}
                className="h-16 rounded-2xl bg-white/15 hover:bg-white/25 border border-white/20 text-white font-bold text-2xl active:scale-95 transition-all shadow"
              >
                {k}
              </button>
            ))}
          </div>
        ) : (
          <div className="flex flex-col gap-2 mb-4">
            <div className="grid grid-cols-10 gap-1.5">
              {keysNum.map(k => (
                <button key={k} onClick={() => handleKeyPress(k)} className="h-12 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white font-bold text-lg active:scale-95">{k}</button>
              ))}
            </div>
            <div className="grid grid-cols-10 gap-1.5">
              {keysAlpha[0].map(k => (
                <button key={k} onClick={() => handleKeyPress(k)} className="h-12 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white font-bold text-lg active:scale-95">{k}</button>
              ))}
            </div>
            <div className="flex justify-center gap-1.5 px-4">
              {keysAlpha[1].map(k => (
                <button key={k} onClick={() => handleKeyPress(k)} className="flex-1 h-12 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white font-bold text-lg active:scale-95">{k}</button>
              ))}
            </div>
            <div className="flex justify-center gap-1.5 px-8">
              {keysAlpha[2].map(k => (
                <button key={k} onClick={() => handleKeyPress(k)} className="flex-1 h-12 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white font-bold text-lg active:scale-95">{k}</button>
              ))}
            </div>
            <div className="flex gap-2 mt-1">
              <button onClick={() => handleKeyPress(' ')} className="flex-1 h-12 rounded-xl bg-white/20 hover:bg-white/30 text-white font-bold text-sm tracking-widest uppercase active:scale-95">Espaço</button>
            </div>
          </div>
        )}

        <div className="flex gap-3">
          <button onClick={() => setVal('')} className="py-3 px-6 bg-pmi-red/80 hover:bg-pmi-red text-white font-bold rounded-xl active:scale-95">Limpar</button>
          <button onClick={handleBackspace} className="py-3 px-6 bg-amber-600/80 hover:bg-amber-600 text-white font-bold rounded-xl active:scale-95">⌫ Apagar</button>
          <button onClick={() => { onConfirm(val); onClose(); }} className="flex-1 py-3 bg-pmi-green hover:opacity-90 text-white font-bold text-lg rounded-xl shadow-xl active:scale-95">✓ Confirmar Digitação</button>
        </div>
      </div>
    </div>
  )
}

// ---------- TriagemScreen (Apple Liquid Glass - Flow em 2 Passos com Numpad e QWERTY ABNT2 Integrados) ----------
function TriagemScreen({ onDone, onBack, theme, resetIdle }) {
  const [step, setStep]           = useState('cpf') // 'cpf' | 'cadastro'
  const [cpf, setCpf]             = useState('')
  const [nome, setNome]           = useState('')
  const [sobrenome, setSobrenome] = useState('')
  const [activeField, setActiveField] = useState('nome') // 'nome' | 'sobrenome'
  const [loading, setLoading]     = useState(false)
  const [error, setError]         = useState('')
  const [welcomeName, setWelcomeName] = useState('')

  const cpfInputRef = useRef(null)
  const nomeInputRef = useRef(null)
  const sobrenomeInputRef = useRef(null)

  // Foco automático e resete do idle timer
  useEffect(() => {
    if (resetIdle) resetIdle(60000)
    if (step === 'cpf' && cpfInputRef.current) {
      setTimeout(() => cpfInputRef.current?.focus(), 100)
    } else if (step === 'cadastro' && nomeInputRef.current) {
      setTimeout(() => nomeInputRef.current?.focus(), 100)
    }
  }, [step, resetIdle])

  // Lookup automático de cliente existente ao atingir 11 dígitos de CPF
  const checkClientByCPF = async (digits) => {
    if (digits.length !== 11) return
    if (!isValidCPF(digits)) {
      setError('CPF inválido. Por favor, verifique os 11 números.')
      return
    }

    setLoading(true)
    setError('')
    try {
      const token = await getToken()
      const res = await fetch(`${BASE_URL}/api/totem/cliente.php?documento=${digits}`, {
        headers: { 'Authorization': `Bearer ${token}` },
      })
      if (res.ok) {
        const data = await res.json()
        const fullNome = (data.nome || '').trim()
        if (fullNome) {
          setWelcomeName(fullNome)
          setTimeout(() => {
            onDone(data.id, fullNome)
          }, 800)
          return
        }
      }
      // Cidadão novo -> Avança para o Passo 2 (Cadastro de Nome/Sobrenome)
      setStep('cadastro')
      setActiveField('nome')
    } catch (e) {
      setStep('cadastro')
      setActiveField('nome')
    } finally {
      setLoading(false)
    }
  }

  // --- Handlers para o Passo 1 (CPF + Numpad) ---
  const handleNumpadClick = (digit) => {
    if (resetIdle) resetIdle(60000)
    setError('')
    const digits = cpf.replace(/\D/g, '')
    if (digits.length >= 11) return
    const newDigits = digits + digit
    const masked = maskCPF(newDigits)
    setCpf(masked)
    if (newDigits.length === 11) {
      checkClientByCPF(newDigits)
    }
  }

  const handleCpfBackspace = () => {
    if (resetIdle) resetIdle(60000)
    setError('')
    const digits = cpf.replace(/\D/g, '')
    if (digits.length === 0) return
    const newDigits = digits.slice(0, -1)
    setCpf(maskCPF(newDigits))
  }

  const handleCpfClear = () => {
    if (resetIdle) resetIdle(60000)
    setError('')
    setCpf('')
  }

  // --- Handlers para o Passo 2 (Nome/Sobrenome + QWERTY ABNT2) ---
  const handleQwertyKey = (char) => {
    if (resetIdle) resetIdle(60000)
    setError('')
    if (activeField === 'nome') {
      setNome(prev => prev + char)
    } else {
      setSobrenome(prev => prev + char)
    }
  }

  const handleQwertyBackspace = () => {
    if (resetIdle) resetIdle(60000)
    setError('')
    if (activeField === 'nome') {
      setNome(prev => prev.slice(0, -1))
    } else {
      setSobrenome(prev => prev.slice(0, -1))
    }
  }

  const handleQwertyClear = () => {
    if (resetIdle) resetIdle(60000)
    setError('')
    if (activeField === 'nome') setNome('')
    else setSobrenome('')
  }

  // Listener global de Teclado Físico / Leitor USB
  useEffect(() => {
    const handleGlobalKeyDown = (e) => {
      if (resetIdle) resetIdle(60000)
      if (step === 'cpf') {
        if (e.key >= '0' && e.key <= '9') {
          handleNumpadClick(e.key)
        } else if (e.key === 'Backspace') {
          handleCpfBackspace()
        } else if (e.key === 'Escape') {
          onBack()
        } else if (e.key === 'Enter') {
          const digits = cpf.replace(/\D/g, '')
          if (digits.length === 11) checkClientByCPF(digits)
        }
      } else if (step === 'cadastro') {
        if (e.key === 'Enter') {
          e.preventDefault()
          if (activeField === 'nome' && nome.trim().length >= 3) {
            setActiveField('sobrenome')
          } else {
            handleFinalSubmit()
          }
        } else if (e.key === 'Escape') {
          setStep('cpf')
        }
      }
    }
    window.addEventListener('keydown', handleGlobalKeyDown)
    return () => window.removeEventListener('keydown', handleGlobalKeyDown)
  }, [step, cpf, nome, sobrenome, activeField, resetIdle])

  // Submissão do Passo 2 (Gravação do Cidadão Novo)
  const handleFinalSubmit = async () => {
    const digits = cpf.replace(/\D/g, '')
    if (digits.length !== 11 || !isValidCPF(digits)) {
      setError('CPF inválido. Retorne ao primeiro passo.')
      setStep('cpf')
      return
    }
    if (!nome.trim() || nome.trim().length < 3) {
      setError('Por favor, informe seu Nome (mínimo 3 letras).')
      setActiveField('nome')
      return
    }
    if (!sobrenome.trim() || sobrenome.trim().length < 2) {
      setError('Por favor, informe seu Sobrenome.')
      setActiveField('sobrenome')
      return
    }

    setLoading(true)
    setError('')
    try {
      const token = await getToken()
      const fullNome = (nome.trim() + ' ' + sobrenome.trim()).toUpperCase()
      const body = {
        nome: fullNome,
        documento: digits,
      }
      const res = await fetch(`${BASE_URL}/api/totem/cliente.php`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
      })
      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Erro ao cadastrar cidadão')
      onDone(data.id, fullNome)
    } catch (e) {
      setError(e.message)
    } finally {
      setLoading(false)
    }
  }

  const digitsCount = cpf.replace(/\D/g, '').length
  const qwertyRows = [
    ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
    ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Ç'],
    ['Z', 'X', 'C', 'V', 'B', 'N', 'M']
  ]

  return (
    <div className="h-full flex flex-col bg-gradient-to-br from-slate-950 via-pmi-blue to-slate-900 p-6 select-none overflow-hidden relative" onClick={() => resetIdle && resetIdle(60000)}>
      
      {/* Background Liquid Glass Glow Elements */}
      <div className="absolute top-1/4 left-10 w-96 h-96 bg-pmi-green/20 rounded-full blur-3xl pointer-events-none" />
      <div className="absolute bottom-10 right-10 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl pointer-events-none" />

      {/* Header com Design Apple Glass */}
      <div className="relative z-10 text-center mb-4">
        {theme.logo_totem
          ? <img src={`/media/config/${theme.logo_totem}`} className="h-12 mx-auto mb-1 object-contain drop-shadow-2xl" alt="logo"/>
          : <div className="w-12 h-12 rounded-2xl bg-pmi-green flex items-center justify-center mx-auto mb-1 shadow-lg shadow-pmi-green/30"><span className="text-white font-black text-xl">PMI</span></div>
        }
        <h2 className="text-white font-bold text-lg tracking-tight">{theme.titulo}</h2>
        <div className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md px-4 py-1 rounded-full border border-white/15 mt-1 shadow-inner">
          <span className="w-2 h-2 rounded-full bg-pmi-green animate-ping" />
          <span className="text-white/80 text-xs font-semibold uppercase tracking-wider">
            {step === 'cpf' ? 'Passo 1 de 2: Digite seu CPF' : 'Passo 2 de 2: Cadastro de Nome (Primeiro Acesso)'}
          </span>
        </div>
      </div>

      {/* Notificação de Boas-Vindas para Cidadão Cadastrado */}
      {welcomeName && (
        <div className="relative z-20 max-w-md mx-auto my-auto p-8 rounded-3xl bg-emerald-950/80 border-2 border-emerald-400/50 backdrop-blur-xl text-center shadow-2xl animate-fadeIn">
          <div className="w-16 h-16 bg-emerald-500/20 text-emerald-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">✓</div>
          <h3 className="text-white text-2xl font-black mb-1">Bem-vindo(a)!</h3>
          <p className="text-emerald-300 font-bold text-lg">{welcomeName}</p>
          <p className="text-white/60 text-xs mt-3">Redirecionando para o catálogo de serviços...</p>
        </div>
      )}

      {!welcomeName && (
        <div className="relative z-10 flex-1 flex max-w-5xl mx-auto w-full items-stretch my-auto gap-6">

                    {/* ==================== PASSO 1: DIGITAÇÃO DO CPF ==================== */}
          {step === 'cpf' && (
            <div className="flex-1 flex flex-col justify-between bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl max-w-3xl mx-auto w-full">
              
              {/* Header do Passo 1 */}
              <div className="flex items-center justify-between border-b border-white/15 pb-3 mb-3">
                <div>
                  <span className="text-pmi-green font-bold text-xs uppercase tracking-widest block">Identificação do Cidadão</span>
                  <h2 className="text-white text-2xl font-black">Informe o seu CPF</h2>
                </div>
                <div className="text-right">
                  <span className="text-white/50 text-[10px] uppercase font-semibold block">Suporte Híbrido</span>
                  <span className="text-white/80 text-xs font-bold bg-white/10 px-3 py-1 rounded-full border border-white/15">
                    ⌨️ Touch + Barcode USB
                  </span>
                </div>
              </div>

              {/* Display do CPF (Estilo Apple Glass) */}
              <div className={`p-4 rounded-2xl border-2 text-center transition-all shadow-xl relative overflow-hidden backdrop-blur-md mb-3 ${
                digitsCount === 11 && isValidCPF(cpf.replace(/\D/g, ''))
                  ? 'bg-pmi-green/20 border-pmi-green text-white shadow-pmi-green/20'
                  : 'bg-black/40 border-white/20 text-white'
              }`}>
                <input
                  ref={cpfInputRef}
                  type="text"
                  readOnly
                  value={cpf || '___.___.___-__'}
                  className="bg-transparent text-center text-4xl font-black tracking-widest text-white w-full focus:outline-none font-mono"
                />
                <div className="flex items-center justify-center gap-2 mt-1">
                  {digitsCount === 0 && <span className="text-xs text-white/50">Digite os 11 números do seu CPF no teclado abaixo</span>}
                  {digitsCount > 0 && digitsCount < 11 && <span className="text-xs text-amber-300 font-semibold">Faltam {11 - digitsCount} dígitos</span>}
                  {digitsCount === 11 && isValidCPF(cpf.replace(/\D/g, '')) && <span className="text-xs text-pmi-green font-bold">✓ CPF Válido — Verificando...</span>}
                </div>
              </div>

              {/* Teclado Numérico Touch Integrado (Mesmo Container Dark Glass do Passo 2) */}
              <div className="bg-black/30 backdrop-blur-md rounded-2xl p-4 border border-white/15 flex flex-col gap-3 my-auto max-w-xl mx-auto w-full">
                <div className="grid grid-cols-3 gap-3">
                  {['1', '2', '3', '4', '5', '6', '7', '8', '9'].map(num => (
                    <button
                      key={num}
                      onClick={() => handleNumpadClick(num)}
                      className="h-14 bg-white/15 hover:bg-white/25 active:bg-pmi-green active:scale-95 border border-white/20 text-white font-black text-2xl rounded-xl flex items-center justify-center shadow-md transition-all backdrop-blur-md"
                    >
                      {num}
                    </button>
                  ))}
                </div>

                {/* Linha Inferior: Limpar, 0 e Apagar */}
                <div className="grid grid-cols-3 gap-3">
                  <button
                    onClick={handleCpfClear}
                    className="h-14 bg-red-500/20 hover:bg-red-500/30 active:bg-red-600 border border-red-400/30 text-red-200 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 active:scale-95 transition-all shadow-md"
                  >
                    <span className="text-base">🧹</span>
                    <span className="uppercase font-black tracking-wider text-[11px]">Limpar</span>
                  </button>

                  <button
                    onClick={() => handleNumpadClick('0')}
                    className="h-14 bg-white/15 hover:bg-white/25 active:bg-pmi-green active:scale-95 border border-white/20 text-white font-black text-2xl rounded-xl flex items-center justify-center shadow-md transition-all backdrop-blur-md"
                  >
                    0
                  </button>

                  <button
                    onClick={handleCpfBackspace}
                    className="h-14 bg-amber-500/20 hover:bg-amber-500/30 active:bg-amber-600 border border-amber-400/30 text-amber-200 font-bold text-xs rounded-xl flex items-center justify-center gap-1.5 active:scale-95 transition-all shadow-md"
                  >
                    <span className="text-base">⌫</span>
                    <span className="uppercase font-black tracking-wider text-[11px]">Apagar</span>
                  </button>
                </div>
              </div>

              {error && (
                <p className="text-red-200 text-xs font-bold bg-red-950/80 p-3 rounded-xl border border-red-500/40 text-center animate-pulse my-2">
                  ⚠️ {error}
                </p>
              )}

              {/* Botão de Navegação do Passo 1 */}
              <div className="flex gap-4 mt-3">
                <button
                  onClick={onBack}
                  className="py-3.5 px-6 bg-white/10 hover:bg-white/20 active:scale-95 text-white font-bold text-base rounded-2xl border border-white/15 transition-all shadow-lg"
                >
                  ← Voltar ao Início
                </button>
              </div>
            </div>
          )}

          {/* ==================== PASSO 2: CADASTRO DE NOME E SOBRENOME (CIDADÃO NOVO) ==================== */}
          {step === 'cadastro' && (
            <div className="flex-1 flex flex-col justify-between bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl">
              
              {/* Header do Passo 2 */}
              <div className="flex items-center justify-between border-b border-white/15 pb-4 mb-4">
                <div>
                  <span className="text-pmi-green font-bold text-xs uppercase tracking-widest block">Primeiro Atendimento</span>
                  <h2 className="text-white text-2xl font-black">Cadastro do Cidadão</h2>
                </div>
                <div className="bg-white/10 px-4 py-2 rounded-2xl border border-white/15 text-right font-mono">
                  <span className="text-white/60 text-[10px] block uppercase">CPF Confirmado</span>
                  <span className="text-pmi-green font-bold text-sm tracking-wider">{cpf} ✓</span>
                </div>
              </div>

              {/* Display dos Campos Ativos (Nome e Sobrenome) */}
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div
                  onClick={() => setActiveField('nome')}
                  className={`p-3.5 rounded-2xl border-2 cursor-pointer transition-all ${
                    activeField === 'nome'
                      ? 'bg-white/20 border-pmi-green text-white shadow-lg'
                      : 'bg-black/20 border-white/20 text-white/70 hover:bg-white/10'
                  }`}
                >
                  <label className="text-[10px] font-bold uppercase tracking-wider block text-white/60 mb-1">
                    1. Nome * {activeField === 'nome' && '👈 Digitando'}
                  </label>
                  <input
                    ref={nomeInputRef}
                    type="text"
                    placeholder="Digite seu Nome..."
                    value={nome}
                    onFocus={() => setActiveField('nome')}
                    onChange={e => setNome(e.target.value.toUpperCase())}
                    className="bg-transparent text-white font-bold text-xl w-full focus:outline-none uppercase"
                  />
                </div>

                <div
                  onClick={() => setActiveField('sobrenome')}
                  className={`p-3.5 rounded-2xl border-2 cursor-pointer transition-all ${
                    activeField === 'sobrenome'
                      ? 'bg-white/20 border-pmi-green text-white shadow-lg'
                      : 'bg-black/20 border-white/20 text-white/70 hover:bg-white/10'
                  }`}
                >
                  <label className="text-[10px] font-bold uppercase tracking-wider block text-white/60 mb-1">
                    2. Sobrenome * {activeField === 'sobrenome' && '👈 Digitando'}
                  </label>
                  <input
                    ref={sobrenomeInputRef}
                    type="text"
                    placeholder="Digite seu Sobrenome..."
                    value={sobrenome}
                    onFocus={() => setActiveField('sobrenome')}
                    onChange={e => setSobrenome(e.target.value.toUpperCase())}
                    className="bg-transparent text-white font-bold text-xl w-full focus:outline-none uppercase"
                  />
                </div>
              </div>

              {/* Teclado Touch QWERTY ABNT2 Integrado Direto na Tela */}
              <div className="bg-black/30 backdrop-blur-md rounded-2xl p-4 border border-white/15 flex flex-col gap-2 my-auto">
                {qwertyRows.map((row, rIdx) => (
                  <div key={rIdx} className="flex justify-center gap-1.5">
                    {row.map(char => (
                      <button
                        key={char}
                        onClick={() => handleQwertyKey(char)}
                        className="flex-1 h-12 bg-white/15 hover:bg-white/25 active:bg-pmi-green active:scale-95 border border-white/20 text-white font-bold text-lg rounded-xl flex items-center justify-center shadow-md transition-all backdrop-blur-md"
                      >
                        {char}
                      </button>
                    ))}
                  </div>
                ))}

                {/* Linha Inferior com Espaço, Limpar e Apagar */}
                <div className="flex gap-2 mt-1">
                  <button
                    onClick={handleQwertyClear}
                    className="px-5 h-12 bg-red-500/20 hover:bg-red-500/30 active:bg-red-600 border border-red-400/30 text-red-200 font-bold text-xs rounded-xl flex items-center justify-center active:scale-95 transition-all shadow-md"
                  >
                    🧹 Limpar
                  </button>

                  <button
                    onClick={() => handleQwertyKey(' ')}
                    className="flex-1 h-12 bg-white/20 hover:bg-white/30 active:bg-pmi-green border border-white/20 text-white font-bold text-sm tracking-widest uppercase rounded-xl flex items-center justify-center active:scale-95 transition-all shadow-md"
                  >
                    ␣ Espaço
                  </button>

                  <button
                    onClick={handleQwertyBackspace}
                    className="px-5 h-12 bg-amber-500/20 hover:bg-amber-500/30 active:bg-amber-600 border border-amber-400/30 text-amber-200 font-bold text-xs rounded-xl flex items-center justify-center active:scale-95 transition-all shadow-md"
                  >
                    ⌫ Apagar
                  </button>
                </div>
              </div>

              {error && (
                <p className="text-red-200 text-xs font-bold bg-red-950/80 p-3 rounded-xl border border-red-500/40 text-center animate-pulse my-2">
                  ⚠️ {error}
                </p>
              )}

              {/* Botões de Navegação do Passo 2 */}
              <div className="flex gap-4 mt-3">
                <button
                  onClick={() => setStep('cpf')}
                  className="py-4 px-6 bg-white/10 hover:bg-white/20 active:scale-95 text-white font-bold text-base rounded-2xl border border-white/15 transition-all shadow-lg"
                >
                  ← Corrigir CPF
                </button>

                <button
                  onClick={handleFinalSubmit}
                  disabled={loading}
                  className="flex-1 py-4 bg-pmi-green hover:opacity-90 active:scale-95 text-white font-bold text-xl rounded-2xl shadow-xl transition-all disabled:opacity-50 shadow-pmi-green/30"
                >
                  {loading ? 'Cadastrando...' : '✓ Confirmar e Escolher Serviço →'}
                </button>
              </div>
            </div>
          )}

        </div>
      )}
    </div>
  )
}


// ---------- Componente ServicesScreen (Paginação de Serviços até 9 por página) ----------
function ServicesScreen({ services, onSelect, onBack, theme }) {
  const ITEMS_PER_PAGE = 9
  const [currentPage, setCurrentPage] = useState(0)

  const totalPages = Math.ceil((services.length || 1) / ITEMS_PER_PAGE)
  const visibleServices = services.slice(currentPage * ITEMS_PER_PAGE, (currentPage + 1) * ITEMS_PER_PAGE)

  return (
    <div className="h-full flex flex-col bg-pmi-blue">
      <MiniHeader theme={theme} />
      <div className="flex-1 flex flex-col p-8 select-none">
        
        <div className="mb-4 flex items-center justify-between">
          <div>
            <span className="text-white/60 text-xs uppercase tracking-widest font-semibold block mb-1">Passo 1 de 2</span>
            <h1 className="text-white text-3xl font-black">Selecione o Serviço</h1>
          </div>
          {totalPages > 1 && (
            <span className="text-white/80 font-bold text-sm bg-white/10 px-4 py-2 rounded-xl border border-white/20">
              Página {currentPage + 1} de {totalPages}
            </span>
          )}
        </div>

        {/* Grid Responsivo de Serviços (Até 9 itens em 3x3) */}
        <div className="flex-1 grid grid-cols-3 gap-4 overflow-y-auto pr-1 items-stretch">
          {services.length === 0 ? (
            <div className="col-span-3 flex flex-col items-center justify-center p-12 bg-white/10 backdrop-blur-md rounded-3xl border border-white/20 text-center my-auto shadow-2xl">
              <span className="text-4xl mb-3">📭</span>
              <h3 className="text-white text-xl font-bold mb-1">Nenhum serviço ativo nesta unidade</h3>
              <p className="text-white/60 text-sm">Não há serviços cadastrados ou habilitados para esta unidade no momento.</p>
            </div>
          ) : (
            visibleServices.map(s => (
            <button
              key={s.id}
              onClick={() => onSelect(s)}
              className="bg-white/10 border-2 border-white/20 hover:border-pmi-green hover:bg-white/15 rounded-2xl p-5 text-left flex flex-col justify-between transition-all active:scale-[0.98] shadow-lg group h-36"
            >
              <div>
                <span className="inline-block bg-pmi-green text-white font-bold text-[10px] px-2.5 py-0.5 rounded-full mb-2 uppercase tracking-wider">
                  {s.sigla || 'SRV'}
                </span>
                <h3 className="text-white font-bold text-lg leading-tight group-hover:text-pmi-green transition-colors line-clamp-2">
                  {s.nome}
                </h3>
              </div>
              {s.descricao && (
                <p className="text-white/50 text-xs mt-2 line-clamp-1">{s.descricao}</p>
              )}
            </button>
          ))
          )}
        </div>

        {/* Paginação e Botão Voltar */}
        <div className="mt-5 flex items-center justify-between">
          <button onClick={onBack}
            className="bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold px-8 py-3.5 rounded-2xl text-base active:scale-95 transition-all">
            ← Voltar
          </button>

          {totalPages > 1 && (
            <div className="flex gap-3">
              <button
                disabled={currentPage === 0}
                onClick={() => setCurrentPage(p => Math.max(0, p - 1))}
                className="bg-white/15 hover:bg-white/25 border border-white/20 text-white font-bold px-6 py-3.5 rounded-2xl disabled:opacity-30 active:scale-95"
              >
                ← Anterior
              </button>

              <button
                disabled={currentPage >= totalPages - 1}
                onClick={() => setCurrentPage(p => Math.min(totalPages - 1, p + 1))}
                className="bg-white/15 hover:bg-white/25 border border-white/20 text-white font-bold px-6 py-3.5 rounded-2xl disabled:opacity-30 active:scale-95"
              >
                Próxima Página →
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}


function isLightColor(colorHex) {
  if (!colorHex || colorHex === 'transparent') return false
  if (colorHex === '#ffffff' || colorHex === '#fff' || colorHex.toLowerCase() === 'white') return true
  let hex = colorHex.replace('#', '')
  if (hex.length === 3) hex = hex.split('').map(c => c + c).join('')
  if (hex.length !== 6) return false
  const r = parseInt(hex.substring(0, 2), 16)
  const g = parseInt(hex.substring(2, 4), 16)
  const b = parseInt(hex.substring(4, 6), 16)
  const brightness = (r * 299 + g * 587 + b * 114) / 1000
  return brightness > 165
}

// ---------- Componente PriorityScreen (Apple Liquid Glass / Auto-Adaptive Grid Edition - Sem Corte de Texto) ----------
function PriorityScreen({ service, priorities, onSelect, onBack, theme }) {
  const total = priorities.length
  const gridColsClass = 
    total <= 2 ? 'grid-cols-1 sm:grid-cols-2 max-w-3xl' :
    total <= 4 ? 'grid-cols-2 max-w-4xl' :
    total <= 6 ? 'grid-cols-2 md:grid-cols-3 max-w-5xl' :
    'grid-cols-3 max-w-6xl'

  return (
    <div className="h-full flex flex-col bg-slate-950 text-white select-none relative overflow-hidden">
      {/* Luz Ambiente de Profundidade 3D (Ambient Glow Liquid Glass) */}
      <div className="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/30 rounded-full blur-3xl pointer-events-none" />
      <div className="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-600/25 rounded-full blur-3xl pointer-events-none" />

      <MiniHeader theme={theme} />

      <div className="flex-1 flex flex-col p-6 md:p-8 select-none relative z-10">
        {/* Cabeçalho Glassmorphic com Pílulas Luminosas */}
        <div className="mb-4 text-center max-w-2xl mx-auto">
          <div className="inline-flex items-center gap-2 bg-white/10 backdrop-blur-xl border border-white/20 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest text-white/90 shadow-lg mb-2">
            <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
            Passo 2 de 2 • Seleção de Prioridade
          </div>
          <h1 className="text-white text-2xl md:text-3xl font-black tracking-tight drop-shadow-md">
            Como você deseja ser atendido?
          </h1>
          <div className="mt-2 inline-flex items-center gap-2 bg-white/5 backdrop-blur-md border border-white/15 px-4 py-1.5 rounded-2xl text-xs text-white/80 shadow-md">
            <span>Serviço selecionado:</span>
            <strong className="text-emerald-400 font-extrabold tracking-wide">{service.nome}</strong>
          </div>
        </div>

        {/* Grid Apple Liquid Glass - Auto-Adaptável, Centralizado e com items-stretch para suportar textos longos */}
        <div className={`flex-1 grid ${gridColsClass} gap-5 items-stretch place-content-center w-full mx-auto my-auto px-2`}>
          {priorities.map(p => {
            const isPrioritario = p.peso > 0
            const bgColor = p.cor || (isPrioritario ? '#024524' : '#0f172a')
            const isLightBg = isLightColor(bgColor)
            const textColorClass = isLightBg ? 'text-slate-900' : 'text-white'

            return (
              <button
                key={p.id}
                onClick={() => onSelect(p)}
                style={{
                  backgroundColor: bgColor,
                  borderColor: isLightBg ? 'rgba(0,0,0,0.15)' : 'rgba(255,255,255,0.25)'
                }}
                className={`relative overflow-hidden group rounded-[28px] p-6 text-left flex flex-col justify-between min-h-[168px] md:min-h-[184px] h-full border transition-all duration-300 ease-out active:scale-[0.97] hover:scale-[1.03] shadow-[0_15px_35px_-10px_rgba(0,0,0,0.6)] hover:shadow-[0_20px_45px_-8px_rgba(0,0,0,0.85)] hover:border-white/50 ${textColorClass}`}
              >
                {/* Reflexo Especular Liquid Glass no Topo e Diagonal */}
                <div className="absolute inset-0 bg-gradient-to-br from-white/25 via-transparent to-black/35 opacity-75 group-hover:opacity-100 transition-opacity pointer-events-none rounded-[28px]" />

                {/* Linha Superior: Badge/Ícone Compacto em Pílula */}
                <div className="relative z-10 w-full flex items-center justify-between gap-2 mb-3">
                  {isPrioritario ? (
                    <span className="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider px-3.5 py-1 rounded-full bg-gradient-to-r from-amber-400 to-yellow-500 text-slate-950 shadow-md shadow-amber-500/20 border border-amber-300/40">
                      <span>⚡</span> Prioritário
                    </span>
                  ) : (
                    <span className={`inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider px-3.5 py-1 rounded-full backdrop-blur-md border ${
                      isLightBg 
                        ? 'bg-slate-900/10 border-slate-900/20 text-slate-900' 
                        : 'bg-white/15 border-white/25 text-white'
                    }`}>
                      <span>🔹</span> Convencional
                    </span>
                  )}
                </div>

                {/* Título do Tile (Altura Harmônica, Esquerda/Central) */}
                <div className="relative z-10 my-auto py-2">
                  <h3 className="text-xl md:text-2xl font-black leading-snug tracking-tight group-hover:translate-x-1 transition-transform duration-300 drop-shadow-sm">
                    {p.nome}
                  </h3>
                </div>

                {/* Descrição da Lei em Barrinha de Vidro Completa (Sem Corte/Line-Clamp) */}
                <div className="relative z-10 w-full mt-3">
                  {p.descricao ? (
                    <div className={`rounded-xl px-3.5 py-2.5 w-full text-[11.5px] md:text-[12px] leading-snug font-medium backdrop-blur-md border transition-colors ${
                      isLightBg
                        ? 'bg-black/5 border-black/10 text-slate-700'
                        : 'bg-black/25 border-white/15 text-white/95 group-hover:border-white/30'
                    }`}>
                      {p.descricao}
                    </div>
                  ) : (
                    <div className="h-2" />
                  )}
                </div>
              </button>
            )
          })}
        </div>

        {/* Rodapé Pílula Liquid Glass Compacta */}
        <div className="mt-4 text-center">
          <button
            onClick={onBack}
            className="inline-flex items-center gap-2.5 bg-white/10 hover:bg-white/20 active:scale-95 border border-white/25 backdrop-blur-xl text-white font-bold px-7 py-3 rounded-full text-sm transition-all duration-200 shadow-xl"
          >
            <span>←</span> Voltar para a Escolha de Serviço
          </button>
        </div>
      </div>
    </div>
  )
}
// ---------- Componente TicketScreen (Oculta Número da Senha / Foco no Nome do Cidadão) ----------
function TicketScreen({ ticket, onPrint, onDone, theme }) {
  const [countdown, setCountdown] = useState(15)

  useEffect(() => {
    const timer = setInterval(() => {
      setCountdown(prev => {
        if (prev <= 1) {
          clearInterval(timer)
          onDone()
          return 0
        }
        return prev - 1
      })
    }, 1000)
    return () => clearInterval(timer)
  }, [onDone])

  const prioritario = ticket.peso > 0

  return (
    <div className="h-full flex flex-col bg-pmi-blue">
      <MiniHeader theme={theme} />
      <div className="flex-1 flex flex-col items-center justify-center p-8 text-center select-none">

        <div className="w-24 h-24 rounded-full bg-pmi-green flex items-center justify-center mb-6 shadow-2xl animate-fadeIn">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" className="w-14 h-14">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>

        {/* Foco Principal no Nome do Cidadão */}
        <p className="text-white/60 text-base uppercase tracking-widest mb-1 font-semibold">Tudo certo,</p>
        <h1 className="text-white font-black text-5xl mb-6 leading-tight uppercase animate-fadeIn drop-shadow-md">
          {ticket.nomeCliente || 'Cidadão'}!
        </h1>

        {/* Card de Detalhes com Serviço e Prioridade */}
        <div className="bg-white/10 border border-white/20 backdrop-blur-sm rounded-3xl px-10 py-6 w-full max-w-md mb-6 animate-fadeIn">
          {prioritario ? (
            <span className="inline-flex items-center gap-2 bg-[#003580] text-white px-5 py-1.5 rounded-full font-bold text-sm mb-3 shadow">
              Atendimento Prioritário
            </span>
          ) : (
            <span className="inline-flex items-center gap-2 bg-white/20 text-white px-5 py-1.5 rounded-full font-semibold text-sm mb-3 shadow">
              Atendimento Normal
            </span>
          )}
          <p className="text-white font-semibold text-lg">
            Serviço: <span className="font-bold">{ticket.servico?.nome}</span>
          </p>
        </div>

        {/* Instrução em Destaque */}
        <div className="max-w-md bg-white/5 p-6 rounded-2xl border border-white/10 mb-6">
          <p className="text-white text-2xl font-bold leading-snug mb-2">
            Aguarde seu nome ser chamado no painel de atendimento.
          </p>
          <p className="text-white/60 text-base">
            Quando chamado, dirija-se ao guichê indicado.
          </p>
        </div>

        {/* Botões */}
        <div className="flex gap-4 mt-2">
          <button onClick={onPrint}
            className="bg-white/15 border border-white/30 text-white px-7 py-3.5 rounded-xl font-semibold text-base active:scale-95 transition-all">
            🖨 Imprimir comprovante
          </button>
          <button onClick={onDone}
            className="bg-pmi-green text-white px-8 py-3.5 rounded-xl font-bold text-base active:scale-95 transition-all">
            Concluir ({countdown}s)
          </button>
        </div>
      </div>
    </div>
  )
}

// ---------- App principal ----------
export default function TotemApp() {
  const [unidadeId, setUnidadeId] = useState(null)
  const [unitError, setUnitError] = useState(null)
  const [loadingUnit, setLoadingUnit] = useState(true)

  const { theme, reloadTheme } = useTheme(unidadeId)

  const [screen, setScreen]               = useState('idle')
  const [services, setServices]           = useState([])
  const [priorities, setPriorities]       = useState([])
  const [selectedService, setSelectedService] = useState(null)
  const [issuedTicket, setIssuedTicket]   = useState(null)
  const [errorMsg, setErrorMsg]           = useState('')
  const [clienteId, setClienteId]         = useState(null)
  const [clienteNome, setClienteNome]     = useState('')
  const idleTimerRef = useRef(null)

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
          if (parts.length >= 2 && parts[0].toLowerCase() !== 'totem' && parts[0].toLowerCase() !== 'painel') {
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

  // Conexão Mercure para comandos remotos
  useEffect(() => {
    let es = null
    const connectSSE = () => {
      const url = new URL(MERCURE_URL)
      url.searchParams.append('topic', '/totens')
      if (unidadeId) url.searchParams.append('topic', '/unidades/' + unidadeId + '/totem')
      es = new EventSource(url.toString())
      es.onmessage = (e) => {
        try {
          const msg = JSON.parse(e.data)
          if (msg.action === 'reload_config') {
            reloadTheme()
          } else if (msg.action === 'reload_window') {
            window.location.reload()
          }
        } catch (err) {}
      }
      es.onerror = () => { es.close(); setTimeout(connectSSE, 5000) }
    }
    if (unidadeId) connectSSE()
    return () => { if (es) es.close() }
  }, [unidadeId, reloadTheme])

  const resetIdle = useCallback((ms = 40000) => {
    clearTimeout(idleTimerRef.current)
    idleTimerRef.current = setTimeout(() => {
      setScreen('idle')
      setSelectedService(null)
      setIssuedTicket(null)
      setClienteId(null)
      setClienteNome('')
    }, ms)
  }, [])

  const loadData = useCallback(async () => {
    if (!unidadeId) return
    try {
      const [svcsRes, priRes] = await Promise.all([
        fetch(`${BASE_URL}/api/totem/servicos.php?unidade_id=${unidadeId}`),
        fetch(`${BASE_URL}/api/totem/prioridades.php?unidade_id=${unidadeId}`),
      ])
      const toArr = d => Array.isArray(d) ? d : (d?.data || [])
      if (svcsRes.ok)  setServices(toArr(await svcsRes.json()))
      if (priRes.ok)   setPriorities(toArr(await priRes.json()))
    } catch (e) {
      console.error('Failed to load data', e)
    }
  }, [unidadeId])

  useEffect(() => { loadData() }, [loadData])

  const handleStart = useCallback(() => {
    loadData()
    setScreen('triagem')
    resetIdle()
  }, [loadData, resetIdle])

  const handleTriagemDone = useCallback((id, nome) => {
    setClienteId(id)
    setClienteNome(nome || '')
    setScreen('services')
    resetIdle()
  }, [resetIdle])

  const handleSelectService = useCallback((service) => {
    setSelectedService(service)
    setScreen('priority')
    resetIdle()
  }, [resetIdle])

  const handleSelectPriority = useCallback(async (priority) => {
    try {
      if (clienteId && selectedService) {
        const checkRes = await fetch(`${BASE_URL}/api/totem/check-limit.php?cliente_id=${clienteId}&servico_id=${selectedService.id}&unidade_id=${unidadeId}`, {
          headers: { 'Authorization': `Bearer ${await getToken()}` }
        });
        if (checkRes.ok) {
          const checkData = await checkRes.json();
          if (!checkData.allowed) {
            setErrorMsg(checkData.reason || 'Emissão bloqueada pelas regras de segurança.');
            setScreen('error');
            resetIdle(10000);
            return;
          }
        }
      }

      const body = {
        unidade: unidadeId,
        servico: selectedService.id,
        prioridade: priority.id,
        ...(clienteId ? { cliente: { id: clienteId, nome: clienteNome, documento: "00000000000" } } : {}),
      }
      const res  = await apiFetch('/api/distribui', {
        method: 'POST',
        body: JSON.stringify(body),
      })
      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Erro ao emitir senha')
      const senhaStr = typeof data.senha === 'object' ? (data.senha?.format || '') : (data.senha || '')
      setIssuedTicket({ ...data, senha: senhaStr, servico: selectedService, peso: priority.peso, nomeCliente: clienteNome })
      setScreen('ticket')
      resetIdle()
    } catch (e) {
      setErrorMsg(e.message)
      setScreen('error')
    }
  }, [selectedService, clienteId, clienteNome, unidadeId, resetIdle])

  const handlePrint = useCallback(async () => {
    if (!issuedTicket) return
    try {
      await fetch(`${PRINTER_URL}/print`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          senha:   issuedTicket.senha,
          servico: issuedTicket.servico?.nome,
          unidade: theme.subtitulo || 'Atendimento',
          titulo:  theme.titulo || 'Sistema de Atendimento — NovoSGA',
          nome:    issuedTicket.nomeCliente || '',
          prioritario: issuedTicket.peso > 0
        }),
      })
    } catch {
      // Impressora offline
    }
  }, [issuedTicket, theme.subtitulo, theme.titulo])

  const handleDone = useCallback(() => {
    clearTimeout(idleTimerRef.current)
    setScreen('idle')
    setSelectedService(null)
    setIssuedTicket(null)
    setClienteNome('')
    setClienteId(null)
  }, [])

  if (loadingUnit) {
    return (
      <div className="w-screen h-screen flex flex-col items-center justify-center bg-slate-900 text-white">
        <div className="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
        <p className="text-lg font-semibold">Carregando totem de atendimento...</p>
      </div>
    )
  }

  if (unitError) {
    return (
      <ErrorScreen 
        message={unitError} 
        onRetry={() => window.location.href = '/'} 
      />
    )
  }

  return (
    <div className="w-screen h-screen overflow-hidden">
      {screen === 'idle'     && <IdleScreen     onStart={handleStart} theme={theme} />}
      {screen === 'triagem'  && <TriagemScreen  onDone={handleTriagemDone} onBack={() => setScreen('idle')} theme={theme} resetIdle={resetIdle} />}
      {screen === 'services' && <ServicesScreen services={services} onSelect={handleSelectService} onBack={() => setScreen('idle')} theme={theme} />}
      {screen === 'priority' && selectedService && (
        <PriorityScreen service={selectedService} priorities={priorities} onSelect={handleSelectPriority} onBack={() => setScreen('services')} theme={theme} />
      )}
      {screen === 'ticket' && issuedTicket && (
        <TicketScreen ticket={issuedTicket} onPrint={handlePrint} onDone={handleDone} theme={theme} />
      )}
      {screen === 'error' && <ErrorScreen message={errorMsg} onRetry={() => setScreen('idle')} />}
    </div>
  )
}
