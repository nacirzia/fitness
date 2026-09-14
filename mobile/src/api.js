import AsyncStorage from '@react-native-async-storage/async-storage';

export const DEFAULT_API = 'https://fitness.technativelabs.com/wp-json/uptown-app/v1';

function isUnusableApi(url) {
  return !url || /10\.0\.2\.2|127\.0\.0\.1|localhost/i.test(url);
}

export async function getApiBase() {
  const stored = await AsyncStorage.getItem('uf.api');
  if (isUnusableApi(stored)) {
    await AsyncStorage.setItem('uf.api', DEFAULT_API);
    return DEFAULT_API;
  }
  return stored.replace(/\/$/, '');
}

export async function setApiBase(url) {
  const clean = (url || DEFAULT_API).replace(/\/$/, '');
  await AsyncStorage.setItem('uf.api', isUnusableApi(clean) ? DEFAULT_API : clean);
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
