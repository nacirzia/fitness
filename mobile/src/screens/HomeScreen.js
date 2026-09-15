import { Image, ImageBackground, ScrollView, StyleSheet, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useAuth } from '../context/AuthContext';
import { colors } from '../theme';
import { photoForWorkout, photos } from '../images';
import FadeIn from '../components/FadeIn';
import PressScale from '../components/PressScale';
import ProgressBar from '../components/ProgressBar';

function todayDay(plan) {
  if (!plan?.days) return null;
  const n = new Date().getDate();
  return plan.days.find((d) => Number(d.day) === n) || plan.days[0];
}

function greeting() {
  const h = new Date().getHours();
  if (h < 12) return 'Good morning';
  if (h < 18) return 'Good afternoon';
  return 'Good evening';
}

export default function HomeScreen({ navigation }) {
  const insets = useSafeAreaInsets();
  const { member, profile, plan } = useAuth();
  const today = todayDay(plan);
  const first = (member?.display_name || 'Athlete').split(' ')[0];

  return (
    <ScrollView style={styles.wrap} contentContainerStyle={{ paddingBottom: 36 }} showsVerticalScrollIndicator={false}>
      <FadeIn>
        <View style={[styles.top, { paddingTop: insets.top + 12 }]}>
          <View>
            <Text style={styles.hello}>{greeting()}</Text>
            <Text style={styles.name}>{first}.</Text>
          </View>
          <Image source={photos.logo} style={styles.logo} />
        </View>
      </FadeIn>

      <FadeIn delay={80} style={{ paddingHorizontal: 20 }}>
        <View style={styles.stats}>
          <Stat label="Weight" value={profile?.weight_kg ? `${profile.weight_kg}` : '—'} unit="kg" />
          <Stat label="Goal" value={(profile?.goal || 'general').replace('_', ' ')} />
          <Stat label="Fuel" value={plan?.calories_target ? `${plan.calories_target}` : '—'} unit="kcal" />
        </View>
      </FadeIn>

      <FadeIn delay={160} style={{ paddingHorizontal: 20, marginTop: 8 }}>
        <PressScale onPress={() => today && navigation.navigate('Day', { day: today })}>
          <ImageBackground source={photoForWorkout(today?.workout)} style={styles.hero} imageStyle={{ borderRadius: 24 }}>
            <LinearGradient colors={['transparent', 'rgba(9,9,11,0.78)']} style={styles.heroShade}>
              <Text style={styles.kicker}>TODAY · DAY {today?.day || '—'}</Text>
              <Text style={styles.heroTitle}>{today?.workout?.title || 'Recovery day'}</Text>
              <Text style={styles.heroMeta}>{today?.workout?.focus || 'Rest'} · {today?.workout?.duration_min || 0} min</Text>
            </LinearGradient>
          </ImageBackground>
        </PressScale>
      </FadeIn>

      <FadeIn delay={240} style={{ paddingHorizontal: 20, marginTop: 18 }}>
        <Text style={styles.section}>Your targets</Text>
        <View style={styles.card}>
          {(plan?.targets || []).length ? (
            (plan.targets || []).map((t) => (
              <View key={t.id || t.label} style={styles.target}>
                <View style={styles.targetRow}>
                  <Text style={styles.targetLabel}>{t.label}</Text>
                  <Text style={styles.targetVal}>{t.current_value} → {t.target_value} {t.unit}</Text>
                </View>
                <ProgressBar value={t.current_value} max={t.target_value} />
              </View>
            ))
          ) : (
            <Text style={styles.muted}>Targets appear once your coach publishes this month’s plan.</Text>
          )}
        </View>
      </FadeIn>
    </ScrollView>
  );
}

function Stat({ label, value, unit }) {
  return (
    <View style={styles.stat}>
      <Text style={styles.statVal} numberOfLines={1}>{value}</Text>
      {unit ? <Text style={styles.unit}>{unit}</Text> : null}
      <Text style={styles.statLab}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  top: { paddingHorizontal: 20, paddingBottom: 18, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  hello: { color: colors.muted, fontWeight: '600' },
  name: { color: colors.ink, fontSize: 34, fontWeight: '900', letterSpacing: -0.8 },
  logo: { width: 42, height: 42, borderRadius: 12 },
  stats: { flexDirection: 'row', gap: 10, marginBottom: 16 },
  stat: { flex: 1, backgroundColor: '#fff', borderRadius: 18, padding: 12, shadowColor: colors.shadow, shadowOpacity: 1, shadowRadius: 10, shadowOffset: { width: 0, height: 6 }, elevation: 2 },
  statVal: { color: colors.ink, fontWeight: '900', fontSize: 16, textTransform: 'capitalize' },
  unit: { color: colors.muted, fontSize: 11, marginTop: 2 },
  statLab: { color: colors.muted, fontSize: 11, marginTop: 6, textTransform: 'uppercase', letterSpacing: 0.4 },
  hero: { height: 240, justifyContent: 'flex-end' },
  heroShade: { flex: 1, justifyContent: 'flex-end', padding: 18, borderRadius: 24 },
  kicker: { color: '#ffb199', fontSize: 11, fontWeight: '800', letterSpacing: 1.4 },
  heroTitle: { color: '#fff', fontSize: 26, fontWeight: '900', marginTop: 4 },
  heroMeta: { color: 'rgba(255,255,255,0.84)', marginTop: 4 },
  section: { fontSize: 18, fontWeight: '800', color: colors.ink, marginBottom: 10 },
  card: { backgroundColor: '#fff', borderRadius: 20, padding: 16, shadowColor: colors.shadow, shadowOpacity: 1, shadowRadius: 12, shadowOffset: { width: 0, height: 8 }, elevation: 2 },
  target: { marginBottom: 14 },
  targetRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 8 },
  targetLabel: { color: colors.ink, fontWeight: '700' },
  targetVal: { color: colors.muted, fontSize: 12 },
  muted: { color: colors.muted },
});
