import { Helmet } from 'react-helmet-async'

import { Separator } from "@/components/ui/separator"


export default function Home() {
  return (
    <>
      <Helmet>
        <title>Home | Plataforma de Cursos</title>
        <meta name="description" content="Bem-vindo à plataforma de cursos online." />
      </Helmet>
      <h1>Home</h1>
      <Separator className="my-6" />
    </>
  );
} 