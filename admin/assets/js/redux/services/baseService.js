import { api } from "./../../config/api";
import { appendToFormData } from "../../helpers/form";

/**
 * Busca todos os registros com filtros, paginação, etc.
 */
export const getAll = (basePath, options = {}, cancelToken = null) => {
  let params = [];
  let params_qs = "";

  Object.keys(options).forEach(key => {
    if (Array.isArray(options[key])) {
      options[key].forEach((item, idx) => {
        params.push(`${key}[${idx}]=${item}`);
      });
    } else {
      params.push(`${key}=${options[key]}`);
    }
  });

  if (params.length) params_qs = `?${params.join("&")}`;

  const config = cancelToken ? { cancelToken } : undefined;
  return api.get(`${basePath}${params_qs}`, config);
};

/**
 * Exporta registros (quando disponível)
 */
export const getExport = (basePath, options = {}, cancelToken = null) => {
  let params = [];
  let params_qs = "";

  Object.keys(options).forEach(key => {
    if (Array.isArray(options[key])) {
      options[key].forEach((item, idx) => {
        params.push(`${key}[${idx}]=${item}`);
      });
    } else {
      params.push(`${key}=${options[key]}`);
    }
  });

  if (params.length) params_qs = `?${params.join("&")}`;

  const config = cancelToken ? { cancelToken } : undefined;
  return api.get(`${basePath}/export${params_qs}`, config);
};

/**
 * Busca um registro pelo UUID
 */
export const show = (basePath, uuid, cancelToken = null) => {
  const config = cancelToken ? { cancelToken } : undefined;
  return api.get(`${basePath}/${uuid}`, config);
};

/**
 * Cria um novo registro
 */
export const create = (basePath, data) => {
  const formData = new FormData();
  Object.keys(data).forEach(key => appendToFormData(formData, key, data[key]));
  return api.post(basePath, formData);
};

/**
 * Atualiza um registro existente
 */
export const edit = (basePath, uuid, data) => {
  const formData = new FormData();
  Object.keys(data).forEach(key => appendToFormData(formData, key, data[key]));
  return api.post(`${basePath}/update/${uuid}`, formData);
};

/**
 * Remove um registro pelo UUID
 */
export const destroy = (basePath, uuid) => api.delete(`${basePath}/${uuid}`);

/**
 * Busca registros para autocomplete
 */
export const getAutocomplete = (basePath, options = {}, cancelToken = null) => {
  let params = [];
  let params_qs = "";

  Object.keys(options).forEach(key => {
    params.push(`${key}=${options[key]}`);
  });

  if (params.length) params_qs = `?${params.join("&")}`;

  const config = cancelToken ? { cancelToken } : undefined;
  return api.get(`${basePath}/autocomplete${params_qs}`, config);
};

/**
 * Busca informações adicionais (quando disponível)
 */
export const getAdditionalInformation = (basePath, options = {}, cancelToken = null) => {
  let params = [];
  let params_qs = "";

  Object.keys(options).forEach(key => {
    params.push(`${key}=${options[key]}`);
  });

  if (params.length) params_qs = `?${params.join("&")}`;

  const config = cancelToken ? { cancelToken } : undefined;
  return api.get(`${basePath}/additional-information${params_qs}`, config);
}; 