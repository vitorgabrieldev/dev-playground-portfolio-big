import About from "./../screens/About";
import Account from "./../screens/Account";
import AccountPassword from "./../screens/AccountPassword";
import Customers from "./../screens/Customers";
import CustomersDeleted from "./../screens/CustomersDeleted";
import Faq from "./../screens/Faq";
import Home from "./../screens/Home";
import Login from "./../screens/Login";
import Logs from "./../screens/Logs";
import Onboardings from "./../screens/Onboardings";
import PrivacyPolicy from "./../screens/PrivacyPolicy";
import PushCity from "./../screens/PushCity";
import PushGeneral from "./../screens/PushGeneral";
import PushState from "./../screens/PushState";
import PushUser from "./../screens/PushUser";
import RecoveryPassword from "./../screens/RecoveryPassword";
import RolesAndPermissions from "./../screens/RolesAndPermissions";
import SettingsGeneral from "./../screens/SettingsGeneral";
import SettingsNotifications from "./../screens/SettingsNotifications";
import SystemLog from "./../screens/SystemLog";
import TermOfUse from "./../screens/TermOfUse";
import Users from "./../screens/Users";
import Banners from "./../screens/Banners";
import News from "./../screens/News";
import ContentsSeeMore from "./../screens/ContentsSeeMore";
import CategoriesTraining from "./../screens/CategoriesTraining";
import Trainings from "./../screens/Trainings";
import Manuals from "./../screens/Manuals";
import ScreenProtectors from "./../screens/ScreenProtectors";
import DoceRiverValley from "./../screens/DoceRiverValley";
import AccessProfiles from "./../screens/AccessProfiles";
import Notifications from "./../screens/Notifications";

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
	{ path: "/settings/notifications", component: SettingsNotifications, exact: true },

	// Institutional
	{ path: "/institutional/onboardings", component: Onboardings, exact: true },
	{ path: "/institutional/aiz", component: About, exact: true },
	{ path: "/institutional/faq", component: Faq, exact: true },
	{ path: "/institutional/privacy-policy", component: PrivacyPolicy, exact: true },
	{ path: "/institutional/terms-of-use", component: TermOfUse, exact: true },
	{ path: "/institutional/banners", component: Banners, exact: true },

	// Cadastro
	{ path: "/register/news", component: News, exact: true },
	{ path: "/register/Contents-see-more", component: ContentsSeeMore, exact: true },
	{ path: "/register/categories-training", component: CategoriesTraining, exact: true },
	{ path: "/register/trainings", component: Trainings, exact: true },
	{ path: "/register/manuals", component: Manuals, exact: true },
	{ path: "/register/screen-protectors", component: ScreenProtectors, exact: true },
	{ path: "/register/doce-river-valley", component: DoceRiverValley, exact: true },
	{ path: "/register/access-profiles", component: AccessProfiles, exact: true },
	{ path: "/register/notifications", component: Notifications, exact: true },

	// Consultas
	{ path: "/list/customers", component: Customers, exact: true },

	// Objetos deletados
	{ path: "/list-deleted/customers-deleted", component: CustomersDeleted, exact: true },

	// Pushs
	{ path: "/push/city", component: PushCity, exact: true },
	{ path: "/push/general", component: PushGeneral, exact: true },
	{ path: "/push/user", component: PushUser, exact: true },
	{ path: "/push/state", component: PushState, exact: true }
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
