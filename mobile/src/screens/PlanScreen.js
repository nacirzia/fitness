import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useAuth } from '../context/AuthContext';
import { colors } from '../theme';

export default function PlanScreen({ navigation }) {
  const { plan } = useAuth();
  if (!plan) {
    return (
      <View style={styles.empty}>
        <Text style={styles.h1}>No plan yet</Text>
        <Text style={styles.p}>Your coach publishes a monthly workout and diet plan in the admin panel.</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.wrap} contentContainerStyle={{ padding: 20, paddingTop: 64 }}>
      <Text style={styles.kicker}>{plan.month}</Text>
      <Text style={styles.h1}>{plan.title}</Text>
      <Text style={styles.p}>{plan.summary}</Text>
      {(plan.days || []).map((day) => (
        <Pressable key={day.day} style={styles.row} onPress={() => navigation.navigate('Day', { day })}>
          <View>
            <Text style={styles.day}>{day.weekday} {day.day}</Text>
            <Text style={styles.title}>{day.workout?.title}</Text>
          </View>
          <Text style={styles.mins}>{day.workout?.duration_min} min</Text>
        </Pressable>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  empty: { flex: 1, backgroundColor: colors.bg, padding: 24, justifyContent: 'center' },
  kicker: { color: colors.accent, fontWeight: '800', letterSpacing: 1.2 },
  h1: { color: colors.text, fontSize: 28, fontWeight: '900', marginVertical: 8 },
  p: { color: colors.muted, marginBottom: 18, lineHeight: 20 },
  row: { backgroundColor: colors.card, borderRadius: 14, padding: 14, marginBottom: 8, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  day: { color: colors.muted, fontSize: 12, textTransform: 'uppercase' },
  title: { color: colors.text, fontWeight: '700', marginTop: 4 },
  mins: { color: colors.accent, fontWeight: '700' },
});
