// App
import AppAbout from "../screens/App/about";
import AppPrivacyPolicy from "../screens/App/privacyPolicy";
import AppTermsOfUse from "../screens/App/termsOfUse";

// Login
import ResetPassword from "./../screens/ResetPassword";
import VerifyAccount from "./../screens/VerifyAccount";

// -----------------------------------------------------------------------------
// Routes
// -----------------------------------------------------------------------------
export const ROUTES = [
	// App
	{path: "/app/institucional/sobre", component: AppAbout},
	{path: "/app/institucional/politica-de-privacidade", component: AppPrivacyPolicy},
	{path: "/app/institucional/termos-de-uso", component: AppTermsOfUse},
	// Auth
	{path: "/password/reset/:type/:token", component: ResetPassword, logged: false},
	// Verify account
	{path: "/email/verify/:token", component: VerifyAccount, logged: false},
];
