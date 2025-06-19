import React, { Component } from "react";
import * as PropTypes from "prop-types";
import { Form, Row, Col, Modal, Switch, Avatar, Tag } from "antd";
import { UserOutlined } from "@ant-design/icons";
import moment from "moment";

import { customerService } from "./../../redux/services";

import { UIDrawerForm } from "./../../components";

const config = {
  externalName: "cliente",
};

class Show extends Component {
  static propTypes = {
    visible: PropTypes.bool.isRequired,
    onClose: PropTypes.func.isRequired,
    external: PropTypes.bool,
  };

  constructor(props) {
    super(props);

    this.state = {
      isLoading: true,
      uuid: 0,
      item: {},
    };
  }

  onOpen = (uuid) => {
    this.state = {
      isLoading: true,
      uuid: 0,
      item: {},
      previewVisible: false,
      previewImage: "",
    };

    customerService
      .show({ uuid })
      .then((response) => {
        let item = response.data.data;

        this.setState(
          {
            isLoading: false,
            item: item,
          },
          () => {
            // Upload
            // if (item.avatar) {
            //   this.upload.setFiles([
            //     {
            //       uuid: item.uuid,
            //       url: item.avatar,
            //       type: "image/jpeg",
            //     },
            //   ]);
            // }
          }
        );
      })
      .catch((data) => {
        Modal.error({
          title: "Ocorreu um erro!",
          content: String(data),
          onOk: () => {
            // Force close
            return this.onClose();
          },
        });
      });
  };

  resetFields = () => {
    this.setState({
      item: {},
    });
  };

  onClose = () => {
    // Reset fields
    this.resetFields();

    // Callback
    this.props.onClose();
  };

  render() {
    const { visible } = this.props;
    const { uuid, isLoading, item } = this.state;

    return (
      <UIDrawerForm
        visible={visible}
        width={500}
        onClose={this.onClose}
        isLoading={isLoading}
        showBtnSave={false}
        title={`Visualizar registro`}
      >
        <Form layout="vertical">
          <Form.Item label="Avatar / Foto">
            {item.avatar ? (
              <Avatar src={item.avatar} size={100} />
            ) : (
              <Avatar icon={<UserOutlined />} size={100} />
            )}
          </Form.Item>

          <Form.Item label="Nome completo">{item.name || "N/A"}</Form.Item>

          <Row gutter={16}>
            <Col xs={24} sm={12}>
              <Form.Item label="CPF">{item.document || "N/A"}</Form.Item>
            </Col>
            <Col xs={24} sm={12}>
              <Form.Item label="CNPJ da empresa">
                {item.cnpj || "N/A"}
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col xs={24} sm={12}>
              <Form.Item label="E-mail">{item.email || "N/A"}</Form.Item>
            </Col>
            <Col xs={24} sm={12}>
              <Form.Item label="Celular">{item.phone || "N/A"}</Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col xs={24} sm={12}>
              <Form.Item label="Nascimento">
                {item.birth_date
                  ? moment(item.birth_date).format("DD/MM/YYYY")
                  : "N/A"}
              </Form.Item>
            </Col>
            <Col xs={24} sm={12}>
              <Form.Item label="Perfil de acesso">
                {item.perfil?.name || "N/A"}
              </Form.Item>
            </Col>
          </Row>

          <Form.Item label="Preferências">
            {item.preferencias && item.preferencias.length > 0
              ? item.preferencias.map((pref) => (
                  <Tag key={pref.uuid} color="blue" style={{ marginBottom: 4 }}>
                    {pref.name}
                  </Tag>
                ))
              : "N/A"}
          </Form.Item>

          <Row gutter={16}>
            <Col xs={24} sm={12}>
              <Form.Item label="Aceite dos Termos de uso">
                {item.accepted_term_of_users_at
                  ? moment(item.accepted_term_of_users_at).format(
                      "DD/MM/YYYY HH:mm"
                    )
                  : "N/A"}
              </Form.Item>
            </Col>
            <Col xs={24} sm={12}>
              <Form.Item label="Aceite da Política de privacidade">
                {item.accepted_policy_privacy_at
                  ? moment(item.accepted_policy_privacy_at).format(
                      "DD/MM/YYYY HH:mm"
                    )
                  : "N/A"}
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col xs={24} sm={12}>
              <Form.Item label="Aceite para receber novidades">
                <Switch disabled checked={!!item.accept_newsletter} />
              </Form.Item>
            </Col>
            <Col xs={24} sm={12}>
              <Form.Item label="Aceite de notificações">
                <Switch disabled checked={!!item.notify_general} />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col xs={24} sm={12}>
              <Form.Item label="Conta verificada?">
                <Switch disabled checked={!!item.account_verified_at} />
              </Form.Item>
            </Col>
            <Col xs={24} sm={12}>
              <Form.Item label="Data e hora do cadastro">
                {item.created_at
                  ? moment(item.created_at).format("DD/MM/YYYY HH:mm")
                  : "N/A"}
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col xs={24} sm={12}>
              <Form.Item label="Última alteração">
                {item.updated_at
                  ? moment(item.updated_at).format("DD/MM/YYYY HH:mm")
                  : "N/A"}
              </Form.Item>
            </Col>
            <Col xs={24} sm={12}>
              <Form.Item label="Ativo">
                <Switch disabled checked={!!item.is_active} />
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </UIDrawerForm>
    );
  }
}

export default Show;
