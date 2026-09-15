import { useState } from 'react';
import { Alert, Image, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import { activities, colors, experiences, goals } from '../theme';
import { photos } from '../images';
import FadeIn from '../components/FadeIn';
import PressScale from '../components/PressScale';
import Chips from '../components/Chips';

export default function ProfileScreen() {
  const insets = useSafeAreaInsets();
  const { profile, saveProfile, logout, member } = useAuth();
  const [form, setForm] = useState({
    height_cm: String(profile?.height_cm || ''),
    weight_kg: String(profile?.weight_kg || ''),
    target_weight_kg: String(profile?.target_weight_kg || ''),
    age: String(profile?.age || ''),
    gender: profile?.gender || '',
    goal: profile?.goal || 'general',
    activity_level: profile?.activity_level || 'moderate',
    experience: profile?.experience || 'beginner',
    injuries: profile?.injuries || '',
  });
  const [busy, setBusy] = useState(false);

  function set(key, value) {
    setForm((prev) => ({ ...prev, [key]: value }));
  }

  async function onSave() {
    setBusy(true);
    try {
      await saveProfile(form);
      Alert.alert('Saved', 'Your coach can rebuild this month’s plan from these numbers.');
    } catch (e) {
      Alert.alert('Could not save', e.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <ScrollView style={styles.wrap} contentContainerStyle={{ paddingTop: insets.top + 8, paddingBottom: 48 }} showsVerticalScrollIndicator={false}>
      <FadeIn style={{ paddingHorizontal: 20 }}>
        <View style={styles.head}>
          <Image source={photos.woman} style={styles.avatar} />
          <View style={{ flex: 1 }}>
            <Text style={styles.h1}>{member?.display_name || 'Member'}</Text>
            <Text style={styles.p}>{member?.email}</Text>
          </View>
        </View>
      </FadeIn>

      <FadeIn delay={80} style={{ paddingHorizontal: 20 }}>
        <Text style={styles.section}>Body</Text>
        <View style={styles.grid}>
          <Field label="Height cm" value={form.height_cm} onChange={(v) => set('height_cm', v)} />
          <Field label="Weight kg" value={form.weight_kg} onChange={(v) => set('weight_kg', v)} />
          <Field label="Target kg" value={form.target_weight_kg} onChange={(v) => set('target_weight_kg', v)} />
          <Field label="Age" value={form.age} onChange={(v) => set('age', v)} />
        </View>

        <Text style={styles.label}>Goal</Text>
        <Chips options={goals} value={form.goal} onChange={(v) => set('goal', v)} />
        <Text style={styles.label}>Activity</Text>
        <Chips options={activities} value={form.activity_level} onChange={(v) => set('activity_level', v)} />
        <Text style={styles.label}>Experience</Text>
        <Chips options={experiences} value={form.experience} onChange={(v) => set('experience', v)} />

        <Text style={styles.label}>Injuries / notes</Text>
        <TextInput style={[styles.input, { height: 90, textAlignVertical: 'top' }]} multiline value={form.injuries} onChangeText={(v) => set('injuries', v)} placeholderTextColor={colors.muted} />

        <PressScale onPress={onSave} disabled={busy} style={[styles.btn, busy && { opacity: 0.6 }]}>
          <Text style={styles.btnText}>{busy ? 'Saving…' : 'Save my details'}</Text>
        </PressScale>
        <PressScale onPress={logout} style={styles.ghost}>
          <Text style={styles.ghostText}>Log out</Text>
        </PressScale>
      </FadeIn>
    </ScrollView>
  );
}

function Field({ label, value, onChange }) {
  return (
    <View style={styles.field}>
      <Text style={styles.fieldLab}>{label}</Text>
      <TextInput style={styles.input} keyboardType="numeric" value={value} onChangeText={onChange} placeholderTextColor={colors.muted} />
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  head: { flexDirection: 'row', gap: 14, alignItems: 'center', marginBottom: 22 },
  avatar: { width: 72, height: 72, borderRadius: 24 },
  h1: { color: colors.ink, fontSize: 26, fontWeight: '900', letterSpacing: -0.4 },
  p: { color: colors.muted, marginTop: 3 },
  section: { fontSize: 18, fontWeight: '800', color: colors.ink, marginBottom: 12 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginBottom: 8 },
  field: { width: '48%', flexGrow: 1 },
  fieldLab: { color: colors.muted, fontSize: 11, fontWeight: '700', textTransform: 'uppercase', marginBottom: 6 },
  label: { color: colors.muted, fontSize: 11, fontWeight: '700', textTransform: 'uppercase', marginBottom: 8, marginTop: 6 },
  input: { backgroundColor: '#fff', color: colors.ink, borderRadius: 14, padding: 12, borderWidth: 1, borderColor: colors.line },
  btn: { backgroundColor: colors.accent, borderRadius: 16, padding: 16, alignItems: 'center', marginTop: 16 },
  btnText: { color: '#fff', fontWeight: '800' },
  ghost: { alignItems: 'center', marginTop: 16, padding: 8 },
  ghostText: { color: colors.muted, fontWeight: '700' },
});
