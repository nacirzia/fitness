import { useState } from 'react';
import { Alert, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import { useAuth } from '../context/AuthContext';
import { colors } from '../theme';

export default function ProfileScreen() {
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
      Alert.alert('Saved', 'Your coach can now rebuild this month’s plan from your latest numbers.');
    } catch (e) {
      Alert.alert('Could not save', e.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <ScrollView style={styles.wrap} contentContainerStyle={{ padding: 20, paddingTop: 64, paddingBottom: 40 }}>
      <Text style={styles.h1}>Your body data</Text>
      <Text style={styles.p}>{member?.email}</Text>
      {[
        ['height_cm', 'Height (cm)'],
        ['weight_kg', 'Weight (kg)'],
        ['target_weight_kg', 'Target weight (kg)'],
        ['age', 'Age'],
        ['gender', 'Gender'],
        ['goal', 'Goal (fat_loss, muscle, endurance, mobility, general)'],
        ['activity_level', 'Activity (low, moderate, high)'],
        ['experience', 'Experience (beginner, intermediate, advanced)'],
      ].map(([key, label]) => (
        <View key={key} style={{ marginBottom: 10 }}>
          <Text style={styles.label}>{label}</Text>
          <TextInput style={styles.input} value={form[key]} onChangeText={(v) => set(key, v)} placeholderTextColor={colors.muted} />
        </View>
      ))}
      <Text style={styles.label}>Injuries / notes</Text>
      <TextInput style={[styles.input, { height: 90 }]} multiline value={form.injuries} onChangeText={(v) => set('injuries', v)} placeholderTextColor={colors.muted} />
      <Pressable style={styles.btn} onPress={onSave} disabled={busy}>
        <Text style={styles.btnText}>{busy ? 'Saving…' : 'Save details'}</Text>
      </Pressable>
      <Pressable style={styles.ghost} onPress={logout}><Text style={styles.ghostText}>Log out</Text></Pressable>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  h1: { color: colors.text, fontSize: 28, fontWeight: '900' },
  p: { color: colors.muted, marginBottom: 18, marginTop: 4 },
  label: { color: colors.muted, fontSize: 12, marginBottom: 6, textTransform: 'uppercase' },
  input: { backgroundColor: colors.card, color: colors.text, borderRadius: 12, padding: 12, borderWidth: 1, borderColor: colors.line },
  btn: { backgroundColor: colors.accent, borderRadius: 12, padding: 16, alignItems: 'center', marginTop: 16 },
  btnText: { color: '#fff', fontWeight: '800' },
  ghost: { alignItems: 'center', marginTop: 18 },
  ghostText: { color: colors.muted },
});
