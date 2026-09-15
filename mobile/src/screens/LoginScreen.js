import { useEffect, useRef, useState } from 'react';
import { Alert, Animated, Image, ImageBackground, KeyboardAvoidingView, Platform, StyleSheet, Text, TextInput, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { StatusBar } from 'expo-status-bar';
import { useAuth } from '../context/AuthContext';
import { DEFAULT_API, setApiBase } from '../api';
import { colors } from '../theme';
import { photos } from '../images';
import FadeIn from '../components/FadeIn';
import PressScale from '../components/PressScale';

export default function LoginScreen() {
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [busy, setBusy] = useState(false);
  const pulse = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    Animated.loop(
      Animated.sequence([
        Animated.timing(pulse, { toValue: 1.04, duration: 1800, useNativeDriver: true }),
        Animated.timing(pulse, { toValue: 1, duration: 1800, useNativeDriver: true }),
      ])
    ).start();
  }, [pulse]);

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

  function fillDemo() {
    setEmail('demo@uptown-fitness.de');
    setPassword('Uptown2026!');
  }

  return (
    <View style={styles.root}>
      <StatusBar style="light" />
      <ImageBackground source={photos.hero} style={styles.hero} resizeMode="cover">
        <LinearGradient colors={['rgba(9,9,11,0.15)', 'rgba(9,9,11,0.72)', '#f6f3ef']} locations={[0, 0.45, 1]} style={styles.shade} />
        <Animated.View style={[styles.logoWrap, { transform: [{ scale: pulse }] }]}>
          <Image source={photos.logo} style={styles.logo} />
        </Animated.View>
        <FadeIn delay={120} style={styles.heroCopy}>
          <Text style={styles.kicker}>YOUR GYM. YOUR RHYTHM.</Text>
          <Text style={styles.heroTitle}>Train with a plan that actually fits your life.</Text>
        </FadeIn>
      </ImageBackground>

      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={styles.sheet}>
        <FadeIn delay={280}>
          <Text style={styles.sheetTitle}>Welcome in</Text>
          <Text style={styles.sheetSub}>Sign in to open this month’s workouts, targets and meals.</Text>
          <TextInput style={styles.input} autoCapitalize="none" keyboardType="email-address" value={email} onChangeText={setEmail} placeholder="Email" placeholderTextColor={colors.muted} />
          <TextInput style={styles.input} secureTextEntry value={password} onChangeText={setPassword} placeholder="Password" placeholderTextColor={colors.muted} />
          <PressScale onPress={onLogin} disabled={busy} style={[styles.btn, busy && { opacity: 0.6 }]}>
            <Text style={styles.btnText}>{busy ? 'Signing in…' : 'Enter the club'}</Text>
          </PressScale>
          <PressScale onPress={fillDemo} style={styles.demo}>
            <Text style={styles.demoText}>Use demo member</Text>
          </PressScale>
        </FadeIn>
      </KeyboardAvoidingView>
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1, backgroundColor: colors.bg },
  hero: { height: '46%', justifyContent: 'flex-end' },
  shade: { ...StyleSheet.absoluteFillObject },
  logoWrap: { position: 'absolute', top: 58, left: 24 },
  logo: { width: 54, height: 54, borderRadius: 14 },
  heroCopy: { padding: 24, paddingBottom: 28 },
  kicker: { color: '#ffb199', fontSize: 11, fontWeight: '800', letterSpacing: 1.6, marginBottom: 8 },
  heroTitle: { color: '#fff', fontSize: 28, fontWeight: '900', lineHeight: 32, letterSpacing: -0.6 },
  sheet: { flex: 1, paddingHorizontal: 22, paddingTop: 8 },
  sheetTitle: { fontSize: 26, fontWeight: '900', color: colors.ink, letterSpacing: -0.4 },
  sheetSub: { color: colors.muted, marginTop: 6, marginBottom: 18, lineHeight: 20 },
  input: { backgroundColor: '#fff', color: colors.ink, borderRadius: 16, padding: 16, marginBottom: 10, borderWidth: 1, borderColor: colors.line, fontSize: 16 },
  btn: { backgroundColor: colors.accent, borderRadius: 16, padding: 17, alignItems: 'center', marginTop: 8, shadowColor: colors.accent, shadowOpacity: 0.28, shadowRadius: 12, shadowOffset: { width: 0, height: 8 } },
  btnText: { color: '#fff', fontWeight: '800', letterSpacing: 0.4, textTransform: 'uppercase', fontSize: 13 },
  demo: { alignItems: 'center', marginTop: 16, padding: 8 },
  demoText: { color: colors.accent, fontWeight: '700' },
});
