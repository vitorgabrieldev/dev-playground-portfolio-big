import { BrowserRouter, Routes, Route } from "react-router-dom";
import { lazy, Suspense } from "react";
import Loader from "@/components/ui/Loader";

const Home = lazy(() => import("./pages/Home"));
const Login = lazy(() => import("./pages/Login"));
const Register = lazy(() => import("./pages/Register"));
const ForgotPassword = lazy(() => import("./pages/ForgotPassword"));
const ConfirmCode = lazy(() => import("./pages/ConfirmCode"));
const ResetPassword = lazy(() => import("./pages/ResetPassword"));
const CoursesList = lazy(() => import("./pages/CoursesList"));
const CoursesFilterSort = lazy(() => import("./pages/CoursesFilterSort"));
const CourseDetails = lazy(() => import("./pages/CourseDetails"));
const CourseConsume = lazy(() => import("./pages/CourseConsume"));
const Profile = lazy(() => import("./pages/Profile"));
const EditProfile = lazy(() => import("./pages/EditProfile"));
const MyPurchasedCourses = lazy(() => import("./pages/MyPurchasedCourses"));
const MyCreatedCourses = lazy(() => import("./pages/MyCreatedCourses"));
const CreatedCourseDetails = lazy(() => import("./pages/CreatedCourseDetails"));
const CreateCourse = lazy(() => import("./pages/CreateCourse"));
const EditCourse = lazy(() => import("./pages/EditCourse"));
const CardsList = lazy(() => import("./pages/CardsList"));
const AddCard = lazy(() => import("./pages/AddCard"));
const EditCard = lazy(() => import("./pages/EditCard"));
const RemoveCard = lazy(() => import("./pages/RemoveCard"));
const ReceivementData = lazy(() => import("./pages/ReceivementData"));
const FinancialStatement = lazy(() => import("./pages/FinancialStatement"));
const WithdrawRequest = lazy(() => import("./pages/WithdrawRequest"));
const WithdrawConfirm = lazy(() => import("./pages/WithdrawConfirm"));
const Checkout = lazy(() => import("./pages/Checkout"));
const PurchaseHistory = lazy(() => import("./pages/PurchaseHistory"));
const PurchaseDetails = lazy(() => import("./pages/PurchaseDetails"));
const PaymentSuccess = lazy(() => import("./pages/PaymentSuccess"));
const PaymentError = lazy(() => import("./pages/PaymentError"));
const Notifications = lazy(() => import("./pages/Notifications"));
const FAQ = lazy(() => import("./pages/FAQ"));
const TicketOpen = lazy(() => import("./pages/TicketOpen"));
const NotificationPreferences = lazy(() => import("./pages/NotificationPreferences"));
const PrivacySettings = lazy(() => import("./pages/PrivacySettings"));
const Language = lazy(() => import("./pages/Language"));
const Terms = lazy(() => import("./pages/Terms"));
const PrivacyPolicy = lazy(() => import("./pages/PrivacyPolicy"));
const CookiesConsent = lazy(() => import("./pages/CookiesConsent"));
const ReportError = lazy(() => import("./pages/ReportError"));
const Feedback = lazy(() => import("./pages/Feedback"));
const ConfirmModal = lazy(() => import("./pages/ConfirmModal"));
const AlertModal = lazy(() => import("./pages/AlertModal"));
const Chat = lazy(() => import("./pages/Chat"));
const Favorites = lazy(() => import("./pages/Favorites"));
const Error = lazy(() => import("./pages/Error"));

export default function App() {
  return (
    <BrowserRouter>
      <Suspense fallback={<Loader />}>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route path="/cadastro" element={<Register />} />
          <Route path="/recuperar-senha" element={<ForgotPassword />} />
          <Route path="/confirmar-codigo" element={<ConfirmCode />} />
          <Route path="/alterar-senha" element={<ResetPassword />} />
          <Route path="/cursos" element={<CoursesList />} />
          <Route path="/cursos/filtros" element={<CoursesFilterSort />} />
          <Route path="/cursos/:id" element={<CourseDetails />} />
          <Route path="/cursos/:id/assistir" element={<CourseConsume />} />
          <Route path="/perfil" element={<Profile />} />
          <Route path="/perfil/editar" element={<EditProfile />} />
          <Route path="/meus-cursos" element={<MyPurchasedCourses />} />
          <Route path="/cursos-criados" element={<MyCreatedCourses />} />
          <Route path="/cursos-criados/:id" element={<CreatedCourseDetails />} />
          <Route path="/cursos/novo" element={<CreateCourse />} />
          <Route path="/cursos/:id/editar" element={<EditCourse />} />
          <Route path="/cartoes" element={<CardsList />} />
          <Route path="/cartoes/novo" element={<AddCard />} />
          <Route path="/cartoes/:id/editar" element={<EditCard />} />
          <Route path="/cartoes/:id/remover" element={<RemoveCard />} />
          <Route path="/recebimento" element={<ReceivementData />} />
          <Route path="/extrato" element={<FinancialStatement />} />
          <Route path="/saque" element={<WithdrawRequest />} />
          <Route path="/saque/confirmar" element={<WithdrawConfirm />} />
          <Route path="/checkout" element={<Checkout />} />
          <Route path="/compras" element={<PurchaseHistory />} />
          <Route path="/compras/:id" element={<PurchaseDetails />} />
          <Route path="/pagamento/sucesso" element={<PaymentSuccess />} />
          <Route path="/pagamento/erro" element={<PaymentError />} />
          <Route path="/notificacoes" element={<Notifications />} />
          <Route path="/faq" element={<FAQ />} />
          <Route path="/ticket" element={<TicketOpen />} />
          <Route path="/notificacoes/preferencias" element={<NotificationPreferences />} />
          <Route path="/privacidade" element={<PrivacySettings />} />
          <Route path="/idioma" element={<Language />} />
          <Route path="/termos" element={<Terms />} />
          <Route path="/privacidade/politica" element={<PrivacyPolicy />} />
          <Route path="/cookies" element={<CookiesConsent />} />
          <Route path="/reportar-erro" element={<ReportError />} />
          <Route path="/feedback" element={<Feedback />} />
          <Route path="/modal/confirmacao" element={<ConfirmModal />} />
          <Route path="/modal/alerta" element={<AlertModal />} />
          <Route path="/chat" element={<Chat />} />
          <Route path="/favoritos" element={<Favorites />} />
          <Route path="*" element={<Error />} />
        </Routes>
      </Suspense>
    </BrowserRouter>
  );
}