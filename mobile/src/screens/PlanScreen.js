import { Image, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import { colors } from '../theme';
import { photoForWorkout } from '../images';
import FadeIn from '../components/FadeIn';
import PressScale from '../components/PressScale';

export default function PlanScreen({ navigation }) {
  const insets = useSafeAreaInsets();
  const { plan } = useAuth();
  if (!plan) {
    return (
      <View style={[styles.empty, { paddingTop: insets.top + 40 }]}>
        <Text style={styles.h1}>Plan incoming</Text>
        <Text style={styles.p}>Your coach publishes a monthly workout and diet once your profile is complete.</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.wrap} contentContainerStyle={{ paddingTop: insets.top + 12, paddingBottom: 36 }} showsVerticalScrollIndicator={false}>
      <FadeIn style={{ paddingHorizontal: 20 }}>
        <Text style={styles.kicker}>{plan.month}</Text>
        <Text style={styles.h1}>{plan.title}</Text>
        <Text style={styles.p}>{plan.summary}</Text>
      </FadeIn>
      {(plan.days || []).map((day, i) => (
        <FadeIn key={day.day} delay={Math.min(i * 30, 240)} style={{ paddingHorizontal: 20 }}>
          <PressScale onPress={() => navigation.navigate('Day', { day })} style={styles.row}>
            <Image source={photoForWorkout(day.workout)} style={styles.thumb} />
            <View style={{ flex: 1 }}>
              <Text style={styles.day}>{day.weekday} · day {day.day}</Text>
              <Text style={styles.title}>{day.workout?.title}</Text>
            </View>
            <Text style={styles.mins}>{day.workout?.duration_min}m</Text>
          </PressScale>
        </FadeIn>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  empty: { flex: 1, backgroundColor: colors.bg, padding: 28 },
  kicker: { color: colors.accent, fontWeight: '800', letterSpacing: 1.3, textTransform: 'uppercase', fontSize: 11 },
  h1: { color: colors.ink, fontSize: 28, fontWeight: '900', marginVertical: 6, letterSpacing: -0.5 },
  p: { color: colors.muted, marginBottom: 16, lineHeight: 21 },
  row: { backgroundColor: '#fff', borderRadius: 18, padding: 10, marginBottom: 10, flexDirection: 'row', alignItems: 'center', gap: 12, shadowColor: colors.shadow, shadowOpacity: 1, shadowRadius: 10, shadowOffset: { width: 0, height: 6 }, elevation: 2 },
  thumb: { width: 62, height: 62, borderRadius: 14 },
  day: { color: colors.muted, fontSize: 11, textTransform: 'uppercase', fontWeight: '700' },
  title: { color: colors.ink, fontWeight: '800', marginTop: 3, fontSize: 15 },
  mins: { color: colors.accent, fontWeight: '800' },
});
