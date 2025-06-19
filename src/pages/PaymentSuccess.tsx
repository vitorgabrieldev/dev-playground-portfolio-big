import { Helmet } from 'react-helmet-async'
export default function PaymentSuccess() {
  return (
    <>
      <Helmet>
        <title>Pagamento Concluído | Plataforma de Cursos</title>
      </Helmet>
      <h1>Pagamento Concluído</h1>
      <p>Mensagem de sucesso do pagamento aqui.</p>
    </>
  );
} 