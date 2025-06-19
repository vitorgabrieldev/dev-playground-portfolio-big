import React, { useState } from "react";
import { connect } from "react-redux";
import { Link, Redirect, useLocation } from "react-router-dom";
import { Button, Form, Input, Typography, Modal } from "antd";
import { authActions } from "../../redux/actions";
import { authService } from "../../redux/services";
import { apiUpdateAccessToken } from "../../config/api";
import { CLIENT_DATA } from "../../config/general";

function Login({ doLogin }) {
	const location = useLocation();
	const { referrer } = location.state || { referrer: { pathname: "/" } };

	const [isSending, setIsSending] = useState(false);
	const [redirect, setRedirect] = useState(false);

	const onFinish = (values) => {
		setIsSending(true);

		const data = {
			...values,
			token_name: `${CLIENT_DATA.browser_name} - ${CLIENT_DATA.os_name}`,
		};

		let access_token;

		authService
			.login(data)
			.then((res) => {
				access_token = res.data.access_token;
				apiUpdateAccessToken(`Bearer ${access_token}`);
				return authService.getUserData();
			})
			.then((res) => {
				doLogin({
					access_token,
					...res.data.data,
				});
				setRedirect(true);
			})
			.catch((err) => {
				Modal.error({
					title: "Login failed",
					content: String(err),
				});
			})
			.finally(() => {
				setIsSending(false);
			});
	};

	if (redirect) {
		return <Redirect to={referrer} />;
	}

	return (
		<div style={{ minHeight: "fit-content", display: "flex", justifyContent: "center", alignItems: "center", background: "#fff", borderRadius: "10px", border: "1px solid #888" }}>
			<div style={{ width: 360, padding: 32 }}>
				<Typography.Title level={3} style={{ textAlign: "center", marginBottom: 24, fontWeight: 500 }}>
					Acessar painel
				</Typography.Title>
				<Form layout="vertical" onFinish={onFinish}>
					<Form.Item
						name="email"
						label="Email"
						rules={[
							{ required: true, message: "Enter your email" },
							{ type: "email", message: "Invalid email" },
						]}
					>
						<Input size="large" placeholder="email@example.com" />
					</Form.Item>
					<Form.Item
						name="password"
						label="Senha"
						rules={[{ required: true, message: "Enter your password" }]}
					>
						<Input.Password size="large" placeholder="••••••••" />
					</Form.Item>
					<Button
						type="primary"
						htmlType="submit"
						block
						size="large"
						loading={isSending}
						style={{ borderRadius: 6, marginTop: "10px" }}
					>
						Entrar
					</Button>
				</Form>
			</div>
		</div>
	);
}

const mapDispatchToProps = (dispatch) => ({
	doLogin: (data) => dispatch(authActions.login(data)),
});

export default connect(null, mapDispatchToProps)(Login);