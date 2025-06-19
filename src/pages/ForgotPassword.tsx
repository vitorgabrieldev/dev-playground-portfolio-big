import { Helmet } from 'react-helmet-async'
import { useState, useRef, useEffect } from 'react'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Link } from 'react-router-dom'

export default function ForgotPassword() {
  const [email, setEmail] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState(false)
  const emailRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    emailRef.current?.focus()
  }, [])

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setError('')
    setSuccess(false)
    if (!email) {
      setError('Preencha o e-mail')
      setLoading(false)
      return
    }
    // Simula envio de e-mail
    setTimeout(() => {
      setSuccess(true)
      setLoading(false)
    }, 1200)
  }

  return (
    <>
      <Helmet>
        <title>Recuperar Senha | Plataforma de Cursos</title>
      </Helmet>
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-background to-muted">
        <form
          onSubmit={handleSubmit}
          className="bg-white dark:bg-card shadow-xl rounded-xl p-8 w-full max-w-md flex flex-col gap-6 border border-border"
          aria-labelledby="forgot-title"
        >
          <h1 id="forgot-title" className="text-2xl font-bold text-center mb-2">Recuperar Senha</h1>
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
                Enviando...
              </span>
            ) : 'Enviar link de recuperação'}
          </Button>
          {error && <span className="text-red-600 text-sm text-center" role="alert">{error}</span>}
          {success && <span className="text-green-600 text-sm text-center" role="status">E-mail de recuperação enviado!</span>}
          <div className="flex flex-col gap-2 mt-4 text-center text-sm">
            <Link to="/login" className="text-primary hover:underline">Voltar para login</Link>
          </div>
        </form>
      </div>
    </>
  );
} 