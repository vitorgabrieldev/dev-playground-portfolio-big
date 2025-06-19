import { Helmet } from 'react-helmet-async'

type ErrorPageProps = {
  title?: string;
  message?: string;
  children?: React.ReactNode;
};

export default function Error({
  title = "Erro: Página não encontrada",
  message = "A página que você procura não existe ou foi movida.",
  children,
}: ErrorPageProps) {
  return (
    <>
      <Helmet>
        <title>{title} | Plataforma de Cursos</title>
        <meta name="description" content={message} />
      </Helmet>
      <div style={{ textAlign: "center", marginTop: 64 }}>
        <h1>{title}</h1>
        <p>{message}</p>
        {children}
      </div>
    </>
  );
} 