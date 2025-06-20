import Account from "./../screens/Account";
import AccountPassword from "./../screens/AccountPassword";
import Home from "./../screens/Home";
import Login from "./../screens/Login";
import Logs from "./../screens/Logs";

import RecoveryPassword from "./../screens/RecoveryPassword";
import RolesAndPermissions from "./../screens/RolesAndPermissions";
import SettingsGeneral from "./../screens/SettingsGeneral";
import SystemLog from "./../screens/SystemLog";
import Users from "./../screens/Users";

import Favorites from "./../screens/Favorites";
import Progress from "./../screens/Progress";
import Reviews from "./../screens/Reviews";
import FinancePayouts from "./../screens/FinancePayouts";
import FinanceLogs from "./../screens/FinanceLogs";
import FinanceBalances from "./../screens/FinanceBalances";
import FinancePaymentMethods from "./../screens/FinancePaymentMethods";
import Customers from "./../screens/Customers";
import CustomersCreate from "./../screens/CustomersCreate";
import Courses from "./../screens/Courses";
import CoursesCreate from "./../screens/CoursesCreate";
import CoursesApproval from "./../screens/CoursesApproval";
import Categories from "./../screens/Categories";
import Lessons from "./../screens/Lessons";
import LessonsMaterials from "./../screens/LessonsMaterials";
import Transactions from "./../screens/Transactions";
import TransactionsDetails from "./../screens/TransactionsDetails";
import NotificationsSend from "./../screens/NotificationsSend";
import NotificationsHistory from "./../screens/NotificationsHistory";
import NotificationsTemplates from "./../screens/NotificationsTemplates";
import Faq from "./../screens/Faq";
import FaqCategories from "./../screens/FaqCategories";
import InstitutionalTerms from "./../screens/InstitutionalTerms";
import InstitutionalPrivacy from "./../screens/InstitutionalPrivacy";
import InstitutionalBanners from "./../screens/InstitutionalBanners";

// -----------------------------------------------------------------------------
// Routes
// -----------------------------------------------------------------------------
/**
 * Private routes for registered user access
 *
 * @type {Array}
 */
export const PRIVATE_ROUTES = [
	// Dashboard
	{ path: "/", component: Home, exact: true },

	// Contas
	{ path: "/account", component: Account, exact: true },
	{ path: "/account/password", component: AccountPassword, exact: true },

	// Administração
	{ path: "/administrator/logs", component: Logs, exact: true },
	{ path: "/administrator/roles-and-permissions", component: RolesAndPermissions, exact: true },
	{ path: "/administrator/system-log", component: SystemLog, exact: true },
	{ path: "/administrator/users", component: Users, exact: true },

	// Configurações
	{ path: "/settings/general", component: SettingsGeneral, exact: true },

	// Clientes
	{ path: "/customers/list", component: Customers, exact: true },
	{ path: "/customers/create", component: CustomersCreate, exact: true },

	// Cursos
	{ path: "/courses/list", component: Courses, exact: true },
	{ path: "/courses/create", component: CoursesCreate, exact: true },
	{ path: "/courses/approval", component: CoursesApproval, exact: true },
	{ path: "/categories/list", component: Categories, exact: true },

	// Aulas/Conteúdos
	{ path: "/lessons/list", component: Lessons, exact: true },
	{ path: "/lessons/materials", component: LessonsMaterials, exact: true },

	// Compras & Transações
	{ path: "/transactions/list", component: Transactions, exact: true },
	{ path: "/transactions/details", component: TransactionsDetails, exact: true },

	// Favoritos
	{ path: "/favorites", component: Favorites, exact: true },

	// Progresso
	{ path: "/progress", component: Progress, exact: true },

	// Avaliações
	{ path: "/reviews", component: Reviews, exact: true },

	// Financeiro
	{ path: "/finance/payouts", component: FinancePayouts, exact: true },
	{ path: "/finance/logs", component: FinanceLogs, exact: true },
	{ path: "/finance/balances", component: FinanceBalances, exact: true },
	{ path: "/finance/payment-methods", component: FinancePaymentMethods, exact: true },

	// Notificações
	{ path: "/notifications/send", component: NotificationsSend, exact: true },
	{ path: "/notifications/history", component: NotificationsHistory, exact: true },
	{ path: "/notifications/templates", component: NotificationsTemplates, exact: true },

	// FAQ
	{ path: "/faq/list", component: Faq, exact: true },
	{ path: "/faq/categories", component: FaqCategories, exact: true },

	// Políticas & Institucional
	{ path: "/institutional/terms", component: InstitutionalTerms, exact: true },
	{ path: "/institutional/privacy", component: InstitutionalPrivacy, exact: true },
	{ path: "/institutional/banners", component: InstitutionalBanners, exact: true },
];

/**
 * Session routes that if logged in need to be redirected to the dashboard
 *
 * @type {Array}
 */
export const SESSION_ROUTES = [
  // Entrar
  {
    path: "/login",
    component: Login,
    exact: true,
  },
  // Recuperar senha
  {
    path: "/recovery-password",
    component: RecoveryPassword,
    exact: true,
  },
];
