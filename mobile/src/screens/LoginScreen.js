import { useState } from 'react';
import { Alert, KeyboardAvoidingView, Platform, Pressable, StyleSheet, Text, TextInput } from 'react-native';
import { useAuth } from '../context/AuthContext';
import { DEFAULT_API, setApiBase } from '../api';
import { colors } from '../theme';

export default function LoginScreen() {
  const { login } = useAuth();
  const [email, setEmail] = useState('demo@uptown-fitness.de');
  const [password, setPassword] = useState('Uptown2026!');
  const [busy, setBusy] = useState(false);

  async function onLogin() {
    setBusy(true);
    try {
      await setApiBase(DEFAULT_API);
      await login(email.trim(), password);
    } catch (e) {
      Alert.alert('Login failed', e.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <KeyboardAvoidingView style={styles.wrap} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <Text style={styles.brand}>UPTOWN</Text>
      <Text style={styles.sub}>Your gym. Your monthly plan.</Text>
      <TextInput style={styles.input} autoCapitalize="none" keyboardType="email-address" value={email} onChangeText={setEmail} placeholder="Email" placeholderTextColor={colors.muted} />
      <TextInput style={styles.input} secureTextEntry value={password} onChangeText={setPassword} placeholder="Password" placeholderTextColor={colors.muted} />
      <Pressable style={styles.btn} onPress={onLogin} disabled={busy}>
        <Text style={styles.btnText}>{busy ? 'Signing in…' : 'Log in'}</Text>
      </Pressable>
      <Text style={styles.hint}>Demo: demo@uptown-fitness.de / Uptown2026!</Text>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg, padding: 28, justifyContent: 'center' },
  brand: { color: colors.text, fontSize: 42, fontWeight: '900', letterSpacing: -1 },
  sub: { color: colors.muted, marginBottom: 28, marginTop: 6 },
  input: { backgroundColor: colors.card, color: colors.text, borderRadius: 12, padding: 14, marginBottom: 12, borderWidth: 1, borderColor: colors.line },
  btn: { backgroundColor: colors.accent, borderRadius: 12, padding: 16, alignItems: 'center', marginTop: 8 },
  btnText: { color: '#fff', fontWeight: '800', letterSpacing: 0.6, textTransform: 'uppercase' },
  hint: { color: colors.muted, marginTop: 18, fontSize: 12 },
});
