import { Helmet } from 'react-helmet-async'
export default function PaymentError() {
  return (
    <>
      <Helmet>
        <title>Pagamento com Erro | Plataforma de Cursos</title>
      </Helmet>
      <h1>Pagamento com Erro</h1>
      <p>Mensagem de erro do pagamento aqui.</p>
    </>
  );
} 