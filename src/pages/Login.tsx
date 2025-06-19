import { Helmet } from 'react-helmet-async'
import { useState, useRef, useEffect } from 'react'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import CryptoJS from 'crypto-js'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState(false)
  const emailRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    emailRef.current?.focus()
  }, [])

  async function handleLogin(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setError('')
    setSuccess(false)
    try {
      const res = await fetch('/api/user')
      const user = await res.json()
      if (user.email === email && password) {
        const secret = import.meta.env.VITE_CRYPTO_SECRET
        const encrypted = CryptoJS.AES.encrypt(JSON.stringify(user), secret).toString()
        localStorage.setItem('user', encrypted)
        setSuccess(true)
      } else {
        setError('E-mail ou senha inválidos')
      }
    } catch {
      setError('Erro ao conectar ao servidor')
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <Helmet>
        <title>Login | Plataforma de Cursos</title>
      </Helmet>
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-background to-muted">
        <form
          onSubmit={handleLogin}
          className="bg-white dark:bg-card shadow-xl rounded-xl p-8 w-full max-w-md flex flex-col gap-6 border border-border"
          aria-labelledby="login-title"
        >
          <h1 id="login-title" className="text-2xl font-bold text-center mb-2">Login</h1>
          <div className="flex flex-col gap-2">
            <label htmlFor="email" className="text-sm font-medium">E-mail</label>
            <Input
              id="email"
              ref={emailRef}
              type="email"
              placeholder="Digite seu e-mail"
              value={email}
              onChange={e => setEmail(e.target.value)}
              required
              autoComplete="username"
              aria-invalid={!!error}
              aria-describedby={error ? 'login-error' : undefined}
            />
          </div>
          <div className="flex flex-col gap-2">
            <label htmlFor="password" className="text-sm font-medium">Senha</label>
            <Input
              id="password"
              type="password"
              placeholder="Digite sua senha"
              value={password}
              onChange={e => setPassword(e.target.value)}
              required
              autoComplete="current-password"
              aria-invalid={!!error}
              aria-describedby={error ? 'login-error' : undefined}
            />
          </div>
          <Button
            type="submit"
            disabled={loading}
            className="w-full mt-2"
            aria-busy={loading}
            aria-live="polite"
          >
            {loading ? (
              <span className="flex items-center justify-center gap-2">
                <svg className="animate-spin h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" /><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" /></svg>
                Entrando...
              </span>
            ) : 'Entrar'}
          </Button>
          {error && <span id="login-error" className="text-red-600 text-sm text-center" role="alert">{error}</span>}
          {success && <span className="text-green-600 text-sm text-center" role="status">Login realizado com sucesso!</span>}
        </form>
      </div>
    </>
  );
} 