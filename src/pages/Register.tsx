import { Helmet } from 'react-helmet-async'
import { useState, useRef, useEffect, type ChangeEvent } from 'react'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useNavigate, useLocation, Link } from 'react-router-dom'
import CryptoJS from 'crypto-js'
import { isValidCPF, validateImage, fetchAddressByCep } from '@/lib/utils'
import Tesseract from 'tesseract.js'
import { useRef as useReactRef } from 'react'
import { useAuth } from '@/context/AuthContext'

function maskCPF(value: string) {
  return value
    .replace(/\D/g, '')
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
    .slice(0, 14)
}

const STORAGE_KEY = 'register_draft'
const SECRET = import.meta.env.VITE_CRYPTO_SECRET

function saveDraft(data: any) {
  if (!SECRET) return
  const encrypted = CryptoJS.AES.encrypt(JSON.stringify(data), SECRET).toString()
  localStorage.setItem(STORAGE_KEY, encrypted)
}
function loadDraft() {
  if (!SECRET) return null
  const encrypted = localStorage.getItem(STORAGE_KEY)
  if (!encrypted) return null
  try {
    const bytes = CryptoJS.AES.decrypt(encrypted, SECRET)
    return JSON.parse(bytes.toString(CryptoJS.enc.Utf8))
  } catch {
    return null
  }
}
function clearDraft() {
  localStorage.removeItem(STORAGE_KEY)
}

export default function Register() {
  const [step, setStep] = useState(1)
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [confirmPassword, setConfirmPassword] = useState('')
  const [birth, setBirth] = useState('')
  const [cpf, setCpf] = useState('')
  const [cpfError, setCpfError] = useState<string | null>(null)
  const [cpfFront, setCpfFront] = useState<File | null>(null)
  const [cpfBack, setCpfBack] = useState<File | null>(null)
  const [cpfFrontPreview, setCpfFrontPreview] = useState<string | null>(null)
  const [cpfBackPreview, setCpfBackPreview] = useState<string | null>(null)
  const [cpfFrontError, setCpfFrontError] = useState<string | null>(null)
  const [cpfBackError, setCpfBackError] = useState<string | null>(null)
  const [cpfFrontOcr, setCpfFrontOcr] = useState<string | null>(null)
  const [address, setAddress] = useState({
    street: '', number: '', complement: '', neighborhood: '', city: '', state: '', zip: ''
  })
  const [cepError, setCepError] = useState<string | null>(null)
  const [loadingStep1, setLoadingStep1] = useState(false)
  const [loadingStep2, setLoadingStep2] = useState(false)
  const [loadingStep3, setLoadingStep3] = useState(false)
  const [loadingCep, setLoadingCep] = useState(false)
  const [loadingCpfFront, setLoadingCpfFront] = useState(false)
  const [loadingCpfBack, setLoadingCpfBack] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState(false)
  const nameRef = useRef<HTMLInputElement>(null)
  const cpfFrontInputRef = useReactRef<HTMLInputElement>(null)
  const cpfBackInputRef = useReactRef<HTMLInputElement>(null)
  const [cpfOcrValue, setCpfOcrValue] = useState<string | null>(null)
  const [cpfOcrDiff, setCpfOcrDiff] = useState<boolean>(false)
  const { setUser } = useAuth()
  const location = useLocation()
  const navigate = useNavigate()

  function base64ToFile(base64: string, filename: string, mimeType = 'image/png') {
    const arr = base64.split(',')
    const bstr = atob(arr[1])
    let n = bstr.length
    const u8arr = new Uint8Array(n)
    while (n--) {
      u8arr[n] = bstr.charCodeAt(n)
    }
    return new File([u8arr], filename, { type: mimeType })
  }

  useEffect(() => {
    const draft = loadDraft()
    if (draft) {
      setName(draft.name || '')
      setEmail(draft.email || '')
      setPassword(draft.password || '')
      setConfirmPassword(draft.confirmPassword || '')
      setBirth(draft.birth || '')
      setCpf(draft.cpf || '')
      setAddress(draft.address || { street: '', number: '', complement: '', neighborhood: '', city: '', state: '', zip: '' })
      setCpfFrontPreview(draft.cpfFrontPreview || null)
      setCpfBackPreview(draft.cpfBackPreview || null)
      setStep(draft.step || 1)
      if (draft.cpfFrontPreview) {
        setCpfFront(base64ToFile(draft.cpfFrontPreview, 'cpf-frente.png'))
      }
      if (draft.cpfBackPreview) {
        setCpfBack(base64ToFile(draft.cpfBackPreview, 'cpf-verso.png'))
      }
    } else {
      const today = new Date()
      today.setFullYear(today.getFullYear() - 18)
      setBirth(today.toISOString().slice(0, 10))
    }
    nameRef.current?.focus()
  }, [])

  useEffect(() => {
    if (cpf && cpf.replace(/\D/g, '').length === 11) {
      setCpfError(isValidCPF(cpf) ? null : 'CPF inválido')
    } else {
      setCpfError(null)
    }
  }, [cpf])

  useEffect(() => {
    if (step === 2) {
      (async () => {
        if (cpfFront) {
          const { error, ocr } = await runImageValidation(cpfFront, setCpfFrontError, setCpfFrontOcr, setCpfFrontPreview, setLoadingCpfFront)
          setCpfFrontError(error)
          setCpfFrontOcr(ocr || null)
        }
        if (cpfBack) {
          const { error } = await runImageValidation(cpfBack, setCpfBackError, undefined, setCpfBackPreview, setLoadingCpfBack)
          setCpfBackError(error)
        }
      })()
    }
  }, [step])

  useEffect(() => {
    const cep = address.zip.replace(/\D/g, '')
    if (cep.length === 8) {
      setCepError(null)
      setLoadingCep(true)
      fetchAddressByCep(cep)
        .then(data => {
          setAddress(a => ({ ...a, ...data }))
          setCepError(null)
        })
        .catch(err => setCepError(err.message))
        .finally(() => setLoadingCep(false))
    } else if (cep.length > 0 && cep.length < 8) {
      setCepError('CEP incompleto')
    } else {
      setCepError(null)
    }
  }, [address.zip])

  async function runImageValidation(
    file: File | null,
    setError: (msg: string | null) => void,
    setOcr?: (msg: string | null) => void,
    setPreview?: (url: string | null) => void,
    setLoading?: (b: boolean) => void
  ): Promise<{ error: string | null, ocr?: string | null }> {
    if (!file) {
      setError(null)
      if (setOcr) setOcr(null)
      if (setPreview) setPreview(null)
      setCpfOcrValue(null)
      setCpfOcrDiff(false)
      return { error: null, ocr: null }
    }
    if (setLoading) setLoading(true)
    const basicError = await validateImage(file)
    setError(basicError)
    if (basicError) {
      if (setPreview) setPreview(null)
      if (setOcr) setOcr(null)
      setCpfOcrValue(null)
      setCpfOcrDiff(false)
      return { error: basicError, ocr: null }
    }
    let ocrResult: string | null = null
    if (setOcr) {
      setOcr('Analisando imagem...')
      try {
        const { data } = await Tesseract.recognize(file, 'por')
        const cpfMatch = data.text.match(/\d{3}\.\d{3}\.\d{3}-\d{2}/)
        if (cpfMatch) {
          const ocrCpf = cpfMatch[0].replace(/\D/g, '')
          setCpfOcrValue(ocrCpf)
          const typedCpf = (typeof cpf === 'string' ? cpf : '').replace(/\D/g, '')
          if (typedCpf && ocrCpf !== typedCpf) {
            ocrResult = 'O CPF da imagem é diferente do digitado.'
            setCpfOcrDiff(true)
            setOcr(ocrResult)
          } else {
            ocrResult = 'CPF detectado na imagem.'
            setCpfOcrDiff(false)
            setOcr(ocrResult)
          }
        } else {
          ocrResult = 'Não foi possível identificar um CPF na imagem.'
          setCpfOcrValue(null)
          setCpfOcrDiff(false)
          setOcr(ocrResult)
        }
      } catch {
        ocrResult = 'Erro ao analisar a imagem.'
        setCpfOcrValue(null)
        setCpfOcrDiff(false)
        setOcr(ocrResult)
      }
    }
    return { error: null, ocr: ocrResult }
  }

  useEffect(() => {
    if (cpfOcrValue) {
      const typedCpf = (typeof cpf === 'string' ? cpf : '').replace(/\D/g, '')
      if (typedCpf && cpfOcrValue !== typedCpf) {
        setCpfOcrDiff(true)
      } else {
        setCpfOcrDiff(false)
      }
    } else {
      setCpfOcrDiff(false)
    }
  }, [cpf, cpfOcrValue])

  async function handleFileChange(e: ChangeEvent<HTMLInputElement>, setFile: (f: File | null) => void, setPreview: (url: string | null) => void, key: 'cpfFrontPreview' | 'cpfBackPreview', setError: (msg: string | null) => void, setOcr?: (msg: string | null) => void) {
    const file = e.target.files?.[0] || null
    setFile(file)
    setError(null)
    if (setOcr) setOcr(null)
    if (file) {
      if (key === 'cpfFrontPreview') {
        const { error, ocr } = await runImageValidation(file, setError, setOcr, setPreview, setLoadingCpfFront)
        setCpfFrontError(error)
        setCpfFrontOcr(ocr || null)
      } else {
        const { error } = await runImageValidation(file, setError, undefined, setPreview, setLoadingCpfBack)
        setCpfBackError(error)
      }
      const reader = new FileReader()
      reader.onload = async () => {
        setPreview(reader.result as string)
        saveDraft({
          name, email, password, confirmPassword, birth, cpf, address, step,
          cpfFrontPreview: key === 'cpfFrontPreview' ? reader.result : cpfFrontPreview,
          cpfBackPreview: key === 'cpfBackPreview' ? reader.result : cpfBackPreview
        })
      }
      reader.readAsDataURL(file)
    } else {
      setPreview(null)
    }
  }

  async function handleNextStep() {
    setError('')
    if (cpfFront) {
      const { error, ocr } = await runImageValidation(cpfFront, setCpfFrontError, setCpfFrontOcr, setCpfFrontPreview, setLoadingCpfFront)
      setCpfFrontError(error)
      setCpfFrontOcr(ocr || null)
      if (error) {
        setError(error)
        return
      }
    }
    if (cpfBack) {
      const { error } = await runImageValidation(cpfBack, setCpfBackError, undefined, setCpfBackPreview, setLoadingCpfBack)
      setCpfBackError(error)
      if (error) {
        setError(error)
        return
      }
    }
    if (step === 1) {
      if (!name || !email || !password || !confirmPassword) {
        setError('Preencha todos os campos')
        return
      }
      if (password !== confirmPassword) {
        setError('As senhas não coincidem')
        return
      }
      setLoadingStep1(true)
      setTimeout(() => {
        saveDraft({
          name, email, password, confirmPassword, birth, cpf, address, step: step + 1, cpfFrontPreview, cpfBackPreview
        })
        setStep(s => s + 1)
        setLoadingStep1(false)
      }, 1000)
      return
    }
    if (step === 2) {
      if (!birth || !cpf || !cpfFrontPreview || !cpfBackPreview) {
        setError('Preencha todos os campos de documento')
        return
      }
      if (cpfError) {
        setError(cpfError)
        return
      }
      if (cpfFrontError || cpfBackError) {
        setError(cpfFrontError || cpfBackError || '')
        return
      }
      setLoadingStep2(true)
      setTimeout(() => {
        saveDraft({
          name, email, password, confirmPassword, birth, cpf, address, step: step + 1, cpfFrontPreview, cpfBackPreview
        })
        setStep(s => s + 1)
        setLoadingStep2(false)
      }, 1000)
      return
    }
    if (step === 3) {
      if (!address.street || !address.number || !address.city || !address.state || !address.zip) {
        setError('Preencha todos os campos de endereço')
        return
      }
      setLoadingStep3(true)
      setTimeout(() => {
        setSuccess(true)
        setLoadingStep3(false)
        clearDraft()
      }, 1200)
    }
  }

  function handlePrevStep() {
    setError('')
    saveDraft({
      name, email, password, confirmPassword, birth, cpf, address, step: step - 1, cpfFrontPreview, cpfBackPreview
    })
    setStep(s => s - 1)
  }

  async function handleRegister(e: React.FormEvent) {
    e.preventDefault()
    setLoadingStep3(true)
    setError('')
    setSuccess(false)
    if (!address.street || !address.number || !address.city || !address.state || !address.zip) {
      setError('Preencha todos os campos de endereço')
      setLoadingStep3(false)
      return
    }
    setTimeout(() => {
      const user = {
        uuid: Date.now().toString(),
        name,
        email,
        avatar: '',
        role: 'user',
        birth,
        cpf,
        address
      }
      if (SECRET) {
        const encrypted = CryptoJS.AES.encrypt(JSON.stringify(user), SECRET).toString()
        localStorage.setItem('user', encrypted)
      }
      setUser(user)
      setSuccess(true)
      setLoadingStep3(false)
      clearDraft()

      const from = (location.state as any)?.from?.pathname || '/'
      navigate(from, { replace: true })
    }, 1200)
  }

  return (
    <>
      <Helmet>
        <title>Cadastro | Plataforma de Cursos</title>
      </Helmet>
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-background to-muted">
        <form
          onSubmit={handleRegister}
          className="bg-white dark:bg-card shadow-xl rounded-xl p-8 w-full max-w-md flex flex-col gap-6 border border-border"
          aria-labelledby="register-title"
        >
          <h1 id="register-title" className="text-2xl font-bold text-center mb-2">Cadastro</h1>
          {step === 1 && (
            <>
              <div className="flex flex-col gap-2">
                <label htmlFor="name" className="text-sm font-medium">Nome <span className="text-red-600 font-bold">*</span></label>
                <Input id="name" ref={nameRef} type="text" placeholder="Digite seu nome" value={name} onChange={e => setName(e.target.value)} required aria-invalid={!!error} />
              </div>
              <div className="flex flex-col gap-2">
                <label htmlFor="email" className="text-sm font-medium">E-mail <span className="text-red-600 font-bold">*</span></label>
                <Input id="email" type="email" placeholder="Digite seu e-mail" value={email} onChange={e => setEmail(e.target.value)} required autoComplete="username" aria-invalid={!!error} />
              </div>
              <div className="flex flex-col gap-2">
                <label htmlFor="password" className="text-sm font-medium">Senha <span className="text-red-600 font-bold">*</span></label>
                <Input id="password" type="password" placeholder="Digite sua senha" value={password} onChange={e => setPassword(e.target.value)} required autoComplete="new-password" aria-invalid={!!error} />
              </div>
              <div className="flex flex-col gap-2">
                <label htmlFor="confirmPassword" className="text-sm font-medium">Confirmar Senha <span className="text-red-600 font-bold">*</span></label>
                <Input id="confirmPassword" type="password" placeholder="Confirme sua senha" value={confirmPassword} onChange={e => setConfirmPassword(e.target.value)} required autoComplete="new-password" aria-invalid={!!error} />
              </div>
              <Button type="button" onClick={handleNextStep} className="w-full mt-2" disabled={loadingStep1}>
                {loadingStep1 ? (
                  <span className="flex items-center justify-center gap-2">
                    <svg className="animate-spin h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" /><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" /></svg>
                    Continuando...
                  </span>
                ) : 'Continuar'}
              </Button>
            </>
          )}
          {step === 2 && (
            <>
              <div className="flex flex-col gap-2">
                <label htmlFor="birth" className="text-sm font-medium">Data de Nascimento <span className="text-red-600 font-bold">*</span></label>
                <Input id="birth" type="date" value={birth} onChange={e => setBirth(e.target.value)} required aria-invalid={!!error} />
              </div>
              <div className="flex flex-col gap-2">
                <label htmlFor="cpf" className="text-sm font-medium">CPF <span className="text-red-600 font-bold">*</span></label>
                <Input id="cpf" type="text" placeholder="000.000.000-00" value={cpf} onChange={e => setCpf(maskCPF(e.target.value))} required aria-invalid={!!cpfError} maxLength={14} />
                {cpfError && <span className="text-red-600 text-xs">{cpfError}</span>}
              </div>
              <div className="flex flex-col gap-2">
                <label className="text-sm font-medium">Imagem do CPF (frente) <span className="text-red-600 font-bold">*</span></label>
                <input
                  ref={cpfFrontInputRef}
                  type="file"
                  accept="image/*"
                  onChange={e => handleFileChange(e, setCpfFront, setCpfFrontPreview, 'cpfFrontPreview', setCpfFrontError, setCpfFrontOcr)}
                  required
                  aria-invalid={!!cpfFrontError}
                  className="hidden"
                />
                <Button
                  type="button"
                  variant="outline"
                  className="cursor-pointer w-full"
                  onClick={() => cpfFrontInputRef.current?.click()}
                  disabled={loadingCpfFront}
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-5">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                  </svg>
                  {loadingCpfFront ? 'Processando...' : 'Selecionar imagem'}
                </Button>
                {cpfFrontPreview && <img src={cpfFrontPreview} alt="Preview CPF frente" className="h-20 object-contain rounded border" />}
                {cpfFrontError && <span className="text-red-600 text-xs">{cpfFrontError}</span>}
                {cpfFrontOcr && <span className="text-xs">{cpfFrontOcr}</span>}
              </div>
              <div className="flex flex-col gap-2">
                <label className="text-sm font-medium">Imagem do CPF (verso) <span className="text-red-600 font-bold">*</span></label>
                <input
                  ref={cpfBackInputRef}
                  type="file"
                  accept="image/*"
                  onChange={e => handleFileChange(e, setCpfBack, setCpfBackPreview, 'cpfBackPreview', setCpfBackError)}
                  required
                  aria-invalid={!!cpfBackError}
                  className="hidden"
                />
                <Button
                  type="button"
                  variant="outline"
                  className="cursor-pointer w-full"
                  onClick={() => cpfBackInputRef.current?.click()}
                  disabled={loadingCpfBack}
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-5">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                  </svg>
                  {loadingCpfBack ? 'Processando...' : 'Selecionar imagem'}
                </Button>
                {cpfBackPreview && <img src={cpfBackPreview} alt="Preview CPF verso" className="h-20 object-contain rounded border" />}
                {cpfBackError && <span className="text-red-600 text-xs">{cpfBackError}</span>}
              </div>
              <div className="flex gap-2">
                <Button type="button" onClick={handlePrevStep} className="w-1/2">Voltar</Button>
                <Button
                  type="button"
                  onClick={handleNextStep}
                  className="w-1/2"
                  disabled={loadingStep2 ||
                    !birth ||
                    !cpf ||
                    !cpfFrontPreview ||
                    !cpfBackPreview ||
                    Boolean(cpfError) ||
                    Boolean(cpfFrontError) ||
                    Boolean(cpfBackError) ||
                    cpfOcrDiff ||
                    (Boolean(cpfFrontPreview) && (!cpfFrontOcr || cpfFrontOcr === 'Analisando imagem...'))
                  }
                >
                  {loadingStep2 ? (
                    <span className="flex items-center justify-center gap-2">
                      <svg className="animate-spin h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" /><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" /></svg>
                      Continuando...
                    </span>
                  ) : 'Continuar'}
                </Button>
              </div>
              {cpfOcrDiff && <span className="text-red-600 text-xs">O CPF digitado é diferente do CPF da imagem.</span>}
            </>
          )}
          {step === 3 && (
            <>
              <div className="flex flex-col gap-2">
                <label className="text-sm font-medium">CEP <span className="text-red-600 font-bold">*</span></label>
                <Input
                  type="text"
                  placeholder="00000-000"
                  value={address.zip}
                  onChange={e => setAddress(a => ({ ...a, zip: e.target.value.replace(/\D/g, '').replace(/(\d{5})(\d{1,3})/, '$1-$2').slice(0, 9) }))}
                  required
                  aria-invalid={!!cepError}
                  maxLength={9}
                  disabled={loadingCep}
                />
                {loadingCep && (
                  <span className="flex items-center gap-2 text-xs text-muted-foreground"><svg className="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" /><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" /></svg>Buscando endereço...</span>
                )}
                {cepError && <span className="text-red-600 text-xs">{cepError}</span>}
              </div>
              <div className="flex flex-col gap-2">
                <label className="text-sm font-medium">Rua <span className="text-red-600 font-bold">*</span></label>
                <Input type="text" placeholder="Rua" value={address.street} disabled required aria-invalid={!!error} />
              </div>
              <div className="flex gap-2">
                <Input type="text" placeholder="Número" value={address.number} onChange={e => setAddress(a => ({ ...a, number: e.target.value }))} required aria-invalid={!!error} className="w-1/2" />
                <Input type="text" placeholder="Complemento" value={address.complement} onChange={e => setAddress(a => ({ ...a, complement: e.target.value }))} className="w-1/2" />
              </div>
              <div className="flex flex-col gap-2">
                <label className="text-sm font-medium">Bairro <span className="text-red-600 font-bold">*</span></label>
                <Input type="text" placeholder="Bairro" value={address.neighborhood} disabled required aria-invalid={!!error} />
              </div>
              <div className="flex gap-2">
                <div className="flex flex-col w-1/2">
                  <label className="text-sm font-medium">Cidade <span className="text-red-600 font-bold">*</span></label>
                  <Input type="text" placeholder="Cidade" value={address.city} disabled required aria-invalid={!!error} />
                </div>
                <div className="flex flex-col w-1/2">
                  <label className="text-sm font-medium">Estado <span className="text-red-600 font-bold">*</span></label>
                  <Input type="text" placeholder="Estado" value={address.state} disabled required aria-invalid={!!error} />
                </div>
              </div>
              <div className="flex gap-2">
                <Button type="button" onClick={handlePrevStep} className="w-1/2">Voltar</Button>
                <Button type="submit" disabled={loadingStep3} className="w-1/2" aria-busy={loadingStep3} aria-live="polite">
                  {loadingStep3 ? (
                    <span className="flex items-center justify-center gap-2">
                      <svg className="animate-spin h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" /><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" /></svg>
                      Finalizando...
                    </span>
                  ) : 'Finalizar cadastro'}
                </Button>
              </div>
            </>
          )}
          {error && <span className="text-red-600 text-sm text-center" role="alert">{error}</span>}
          {success && <span className="text-green-600 text-sm text-center" role="status">Cadastro realizado com sucesso!</span>}
          <div className="flex flex-col gap-2 mt-4 text-center text-sm">
            <Link to="/login" className="text-primary hover:underline">Já tem uma conta? Entrar</Link>
          </div>
        </form>
      </div>
    </>
  );
} 