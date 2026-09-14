import React, { createContext, useContext, useEffect, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { api } from '../api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [bootstrapping, setBootstrapping] = useState(true);
  const [token, setToken] = useState(null);
  const [member, setMember] = useState(null);
  const [profile, setProfile] = useState(null);
  const [plan, setPlan] = useState(null);

  async function hydrate(nextToken, payload) {
    setToken(nextToken);
    setMember(payload.member);
    setProfile(payload.profile);
    setPlan(payload.plan);
    await AsyncStorage.setItem('uf.token', nextToken);
  }

  async function login(email, password) {
    const data = await api('/login', { method: 'POST', body: { email, password } });
    await hydrate(data.token, data);
    return data;
  }

  async function logout() {
    setToken(null);
    setMember(null);
    setProfile(null);
    setPlan(null);
    await AsyncStorage.removeItem('uf.token');
  }

  async function refresh() {
    if (!token) return;
    const data = await api('/me', { token });
    setMember(data.member);
    setProfile(data.profile);
    setPlan(data.plan);
  }

  async function saveProfile(fields) {
    const saved = await api('/profile', { method: 'PUT', body: fields, token });
    setProfile(saved);
    return saved;
  }

  useEffect(() => {
    (async () => {
      try {
        const stored = await AsyncStorage.getItem('uf.token');
        if (stored) {
          const data = await api('/me', { token: stored });
          setToken(stored);
          setMember(data.member);
          setProfile(data.profile);
          setPlan(data.plan);
        }
      } catch (e) {
        await AsyncStorage.removeItem('uf.token');
      } finally {
        setBootstrapping(false);
      }
    })();
  }, []);

  return (
    <AuthContext.Provider value={{ bootstrapping, token, member, profile, plan, login, logout, refresh, saveProfile, setPlan }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
