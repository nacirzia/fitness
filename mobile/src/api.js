import AsyncStorage from '@react-native-async-storage/async-storage';

const DEFAULT_API = 'http://10.0.2.2/uptownfitness/wp-json/uptown-app/v1';

export async function getApiBase() {
  return (await AsyncStorage.getItem('uf.api')) || DEFAULT_API;
}

export async function setApiBase(url) {
  await AsyncStorage.setItem('uf.api', url.replace(/\/$/, ''));
}

export async function api(path, { method = 'GET', body, token } = {}) {
  const base = await getApiBase();
  const headers = { 'Content-Type': 'application/json' };
  if (token) headers.Authorization = `Bearer ${token}`;
  const res = await fetch(`${base}${path}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : undefined,
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data.message || 'Request failed');
    err.status = res.status;
    throw err;
  }
  return data;
}
