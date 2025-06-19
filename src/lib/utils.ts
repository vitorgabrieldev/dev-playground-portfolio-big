import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

// Validação de CPF (algoritmo oficial)
export function isValidCPF(cpf: string): boolean {
  cpf = cpf.replace(/\D/g, '')
  if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false
  let sum = 0, rest
  for (let i = 1; i <= 9; i++) sum += parseInt(cpf.substring(i - 1, i)) * (11 - i)
  rest = (sum * 10) % 11
  if (rest === 10 || rest === 11) rest = 0
  if (rest !== parseInt(cpf.substring(9, 10))) return false
  sum = 0
  for (let i = 1; i <= 10; i++) sum += parseInt(cpf.substring(i - 1, i)) * (12 - i)
  rest = (sum * 10) % 11
  if (rest === 10 || rest === 11) rest = 0
  if (rest !== parseInt(cpf.substring(10, 11))) return false
  return true
}

// Validação básica de imagem (tipo, tamanho, resolução mínima)
export async function validateImage(file: File, options?: { maxSizeMB?: number, minWidth?: number, minHeight?: number }): Promise<string | null> {
  const { maxSizeMB = 5, minWidth = 400, minHeight = 250 } = options || {}
  if (!file.type.startsWith('image/')) return 'O arquivo deve ser uma imagem.'
  if (file.size > maxSizeMB * 1024 * 1024) return `A imagem deve ter no máximo ${maxSizeMB}MB.`
  const img = document.createElement('img')
  const objectUrl = URL.createObjectURL(file)
  return new Promise(resolve => {
    img.onload = () => {
      if (img.width < minWidth || img.height < minHeight) {
        resolve(`A imagem deve ter pelo menos ${minWidth}x${minHeight} pixels.`)
      } else {
        resolve(null)
      }
      URL.revokeObjectURL(objectUrl)
    }
    img.onerror = () => {
      resolve('Não foi possível ler a imagem.')
      URL.revokeObjectURL(objectUrl)
    }
    img.src = objectUrl
  })
}

// Busca endereço pelo CEP usando a API ViaCEP
export async function fetchAddressByCep(cep: string) {
  cep = cep.replace(/\D/g, '')
  if (cep.length !== 8) throw new Error('CEP inválido')
  const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
  if (!res.ok) throw new Error('Erro ao buscar CEP')
  const data = await res.json()
  if (data.erro) throw new Error('CEP não encontrado')
  return {
    street: data.logradouro || '',
    neighborhood: data.bairro || '',
    city: data.localidade || '',
    state: data.uf || '',
    zip: data.cep || cep
  }
}
