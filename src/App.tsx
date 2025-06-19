import { BrowserRouter, Routes, Route } from "react-router-dom"
import { lazy, Suspense } from "react"
import Loader from "@/components/ui/Loader"
import { AuthProvider } from "@/context/AuthContext"
import PrivateRoute from "@/routes/PrivateRoute"
import GuestRoute from "@/routes/GuestRoute"

// Public Pages
const FAQ = lazy(() => import("./pages/FAQ"))
const Terms = lazy(() => import("./pages/Terms"))
const PrivacyPolicy = lazy(() => import("./pages/PrivacyPolicy"))
const CookiesConsent = lazy(() => import("./pages/CookiesConsent"))
const Language = lazy(() => import("./pages/Language"))
const ReportError = lazy(() => import("./pages/ReportError"))
const Feedback = lazy(() => import("./pages/Feedback"))
const CoursesList = lazy(() => import("./pages/CoursesList"))
const CoursesFilterSort = lazy(() => import("./pages/CoursesFilterSort"))
const CourseDetails = lazy(() => import("./pages/CourseDetails"))

// Guest Pages
const Login = lazy(() => import("./pages/Login"))
const Register = lazy(() => import("./pages/Register"))
const ForgotPassword = lazy(() => import("./pages/ForgotPassword"))
const ConfirmCode = lazy(() => import("./pages/ConfirmCode"))
const ResetPassword = lazy(() => import("./pages/ResetPassword"))

// Private Pages
const Home = lazy(() => import("./pages/Home"))
const CourseConsume = lazy(() => import("./pages/CourseConsume"))
const Notifications = lazy(() => import("./pages/Notifications"))
const NotificationPreferences = lazy(() => import("./pages/NotificationPreferences"))
const Chat = lazy(() => import("./pages/Chat"))
const Favorites = lazy(() => import("./pages/Favorites"))
const PurchaseHistory = lazy(() => import("./pages/PurchaseHistory"))
const PurchaseDetails = lazy(() => import("./pages/PurchaseDetails"))
const Checkout = lazy(() => import("./pages/Checkout"))
const PaymentSuccess = lazy(() => import("./pages/PaymentSuccess"))
const PaymentError = lazy(() => import("./pages/PaymentError"))
const Profile = lazy(() => import("./pages/Profile"))
const EditProfile = lazy(() => import("./pages/EditProfile"))
const MyPurchasedCourses = lazy(() => import("./pages/MyPurchasedCourses"))
const MyCreatedCourses = lazy(() => import("./pages/MyCreatedCourses"))
const CreatedCourseDetails = lazy(() => import("./pages/CreatedCourseDetails"))
const CreateCourse = lazy(() => import("./pages/CreateCourse"))
const EditCourse = lazy(() => import("./pages/EditCourse"))
const CardsList = lazy(() => import("./pages/CardsList"))
const AddCard = lazy(() => import("./pages/AddCard"))
const EditCard = lazy(() => import("./pages/EditCard"))
const RemoveCard = lazy(() => import("./pages/RemoveCard"))
const ReceivementData = lazy(() => import("./pages/ReceivementData"))
const FinancialStatement = lazy(() => import("./pages/FinancialStatement"))
const WithdrawRequest = lazy(() => import("./pages/WithdrawRequest"))
const WithdrawConfirm = lazy(() => import("./pages/WithdrawConfirm"))

// Other
const TicketOpen = lazy(() => import("./pages/TicketOpen"))
const ConfirmModal = lazy(() => import("./pages/ConfirmModal"))
const AlertModal = lazy(() => import("./pages/AlertModal"))
const Error = lazy(() => import("./pages/Error"))

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Suspense fallback={<Loader />}>
          <Routes>

            {/* ===================== PUBLIC ===================== */}
            <Route path="/faq" element={<FAQ />} />
            <Route path="/termos" element={<Terms />} />
            <Route path="/privacidade/politica" element={<PrivacyPolicy />} />
            <Route path="/cookies" element={<CookiesConsent />} />
            <Route path="/idioma" element={<Language />} />
            <Route path="/reportar-erro" element={<ReportError />} />
            <Route path="/feedback" element={<Feedback />} />
            <Route path="/cursos" element={<CoursesList />} />
            <Route path="/cursos/filtros" element={<CoursesFilterSort />} />
            <Route path="/cursos/:id" element={<CourseDetails />} />

            {/* ===================== GUEST ===================== */}
            <Route path="/login" element={<GuestRoute><Login /></GuestRoute>} />
            <Route path="/cadastro" element={<GuestRoute><Register /></GuestRoute>} />
            <Route path="/recuperar-senha" element={<GuestRoute><ForgotPassword /></GuestRoute>} />
            <Route path="/confirmar-codigo" element={<GuestRoute><ConfirmCode /></GuestRoute>} />
            <Route path="/alterar-senha" element={<GuestRoute><ResetPassword /></GuestRoute>} />

            {/* ===================== PRIVATE ===================== */}
            <Route path="/" element={<PrivateRoute><Home /></PrivateRoute>} />

            {/* Cursos */}
            <Route path="/cursos/:id/assistir" element={<PrivateRoute><CourseConsume /></PrivateRoute>} />
            <Route path="/meus-cursos" element={<PrivateRoute><MyPurchasedCourses /></PrivateRoute>} />
            <Route path="/cursos-criados" element={<PrivateRoute><MyCreatedCourses /></PrivateRoute>} />
            <Route path="/cursos-criados/:id" element={<PrivateRoute><CreatedCourseDetails /></PrivateRoute>} />
            <Route path="/cursos/novo" element={<PrivateRoute><CreateCourse /></PrivateRoute>} />
            <Route path="/cursos/:id/editar" element={<PrivateRoute><EditCourse /></PrivateRoute>} />

            {/* Perfil */}
            <Route path="/perfil" element={<PrivateRoute><Profile /></PrivateRoute>} />
            <Route path="/perfil/editar" element={<PrivateRoute><EditProfile /></PrivateRoute>} />

            {/* Financeiro */}
            <Route path="/cartoes" element={<PrivateRoute><CardsList /></PrivateRoute>} />
            <Route path="/cartoes/novo" element={<PrivateRoute><AddCard /></PrivateRoute>} />
            <Route path="/cartoes/:id/editar" element={<PrivateRoute><EditCard /></PrivateRoute>} />
            <Route path="/cartoes/:id/remover" element={<PrivateRoute><RemoveCard /></PrivateRoute>} />
            <Route path="/extrato" element={<PrivateRoute><FinancialStatement /></PrivateRoute>} />
            <Route path="/recebimento" element={<PrivateRoute><ReceivementData /></PrivateRoute>} />
            <Route path="/saque" element={<PrivateRoute><WithdrawRequest /></PrivateRoute>} />
            <Route path="/saque/confirmar" element={<PrivateRoute><WithdrawConfirm /></PrivateRoute>} />

            {/* Compras */}
            <Route path="/compras" element={<PrivateRoute><PurchaseHistory /></PrivateRoute>} />
            <Route path="/compras/:id" element={<PrivateRoute><PurchaseDetails /></PrivateRoute>} />
            <Route path="/checkout" element={<PrivateRoute><Checkout /></PrivateRoute>} />
            <Route path="/pagamento/sucesso" element={<PrivateRoute><PaymentSuccess /></PrivateRoute>} />
            <Route path="/pagamento/erro" element={<PrivateRoute><PaymentError /></PrivateRoute>} />

            {/* Outros */}
            <Route path="/notificacoes" element={<PrivateRoute><Notifications /></PrivateRoute>} />
            <Route path="/notificacoes/preferencias" element={<PrivateRoute><NotificationPreferences /></PrivateRoute>} />
            <Route path="/chat" element={<PrivateRoute><Chat /></PrivateRoute>} />
            <Route path="/favoritos" element={<PrivateRoute><Favorites /></PrivateRoute>} />
            <Route path="/ticket" element={<PrivateRoute><TicketOpen /></PrivateRoute>} />

            {/* Modais e extras */}
            <Route path="/modal/confirmacao" element={<PrivateRoute><ConfirmModal /></PrivateRoute>} />
            <Route path="/modal/alerta" element={<PrivateRoute><AlertModal /></PrivateRoute>} />

            {/* ===================== 404 ===================== */}
            <Route path="*" element={<Error />} />

          </Routes>
        </Suspense>
      </BrowserRouter>
    </AuthProvider>
  )
}