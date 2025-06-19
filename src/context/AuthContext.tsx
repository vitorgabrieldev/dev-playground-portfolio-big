import { createContext, useContext, useEffect, useState, type ReactNode } from 'react'
import CryptoJS from 'crypto-js'

interface User {
  uuid: string
  name: string
  email: string
  avatar: string
  role: string
  [key: string]: any
}

interface AuthContextType {
  user: User | null
  isAuthenticated: boolean
  setUser: (user: User | null) => void
  logout: () => void
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)

  useEffect(() => {
    const secret = import.meta.env.VITE_CRYPTO_SECRET
    const encrypted = localStorage.getItem('user')
    if (encrypted && secret) {
      try {
        const bytes = CryptoJS.AES.decrypt(encrypted, secret)
        const data = JSON.parse(bytes.toString(CryptoJS.enc.Utf8))
        setUser(data)
      } catch {
        setUser(null)
      }
    }
  }, [])

  function logout() {
    localStorage.removeItem('user')
    setUser(null)
  }

  return (
    <AuthContext.Provider value={{ user, isAuthenticated: !!user, setUser, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth deve ser usado dentro de AuthProvider')
  return ctx
} 