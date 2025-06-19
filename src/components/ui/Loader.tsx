import { Skeleton } from "@/components/ui/skeleton";
import logo from "@/assets/react.svg";

export default function Loader({ message = "Carregando sua experiência de aprendizado..." }) {
  return (
    <div style={{
      minHeight: "100vh",
      display: "flex",
      flexDirection: "column",
      alignItems: "center",
      justifyContent: "center",
      background: "var(--background)",
      color: "var(--foreground)",
      gap: 24,
    }}>
      <img
        src={logo}
        alt="Logo"
        style={{ width: 72, height: 72, animation: "spin 1.5s linear infinite" }}
      />
      <p style={{ fontSize: 18, fontWeight: 500 }}>{message}</p>
      <div style={{ width: 320, display: "flex", flexDirection: "column", gap: 12 }}>
        <Skeleton style={{ height: 32, width: "100%" }} />
        <Skeleton style={{ height: 20, width: "80%" }} />
        <Skeleton style={{ height: 20, width: "90%" }} />
        <Skeleton style={{ height: 20, width: "60%" }} />
      </div>
      <style>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
} 