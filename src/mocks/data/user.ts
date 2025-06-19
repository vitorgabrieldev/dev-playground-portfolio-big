export const mockUser = {
  uuid: "b1a2c3d4-e5f6-7890-abcd-1234567890ef",
  name: "Aluno Teste",
  email: "vitorgabrieldeoliveiradev@gmail.com",
  avatar: "https://github.com/shadcn.png",
  role: "student",
  purchasedCourses: [
    "c1a2b3c4-d5e6-7890-abcd-1234567890ab",
    "c2b3a4d5-e6f7-8901-bcda-2345678901bc"
  ],
  createdCourses: [
    "c3c4b5a6-f7e8-9012-cdab-3456789012cd"
  ],
  notifications: [
    { uuid: "n1a2b3c4-d5e6-7890-abcd-1234567890aa", message: "Bem-vindo à plataforma!", read: false, date: "2024-06-01" },
    { uuid: "n2b3a4c5-e6f7-8901-bcda-2345678901bb", message: "Seu curso foi aprovado!", read: true, date: "2024-06-02" }
  ],
  cards: [
    { uuid: "card-1a2b-3c4d-5e6f-7890abcdef01", brand: 'Visa', last4: '1234', holder: 'Aluno Teste', exp: '12/28' },
    { uuid: "card-2b3a-4c5d-6e7f-8901bcdef012", brand: 'Mastercard', last4: '5678', holder: 'Aluno Teste', exp: '11/27' }
  ],
  financial: {
    balance: 1000,
    receivables: 200,
    withdrawable: 800,
    statement: [
      { uuid: "f1a2b3c4-d5e6-7890-abcd-1234567890fa", type: 'recebimento', value: 200, date: '2024-06-01', description: 'Venda de curso React' },
      { uuid: "f2b3a4c5-e6f7-8901-bcda-2345678901fb", type: 'saque', value: -100, date: '2024-06-02', description: 'Saque bancário' }
    ]
  },
  preferences: {
    notifications: true,
    darkMode: false,
    language: "pt-BR"
  },
  address: {
    street: "Rua Exemplo",
    number: "123",
    city: "São Paulo",
    state: "SP",
    zip: "01000-000"
  },
  phone: "+55 11 99999-9999",
  createdAt: "2024-01-01T12:00:00Z",
  updatedAt: "2024-06-01T12:00:00Z"
};
