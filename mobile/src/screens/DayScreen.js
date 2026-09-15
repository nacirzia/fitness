import { ImageBackground, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { colors } from '../theme';
import { photoForWorkout, photos } from '../images';
import FadeIn from '../components/FadeIn';

export default function DayScreen({ navigation, route }) {
  const insets = useSafeAreaInsets();
  const day = route.params?.day || {};
  const workout = day.workout || {};
  const meals = day.meals || [];

  return (
    <ScrollView style={styles.wrap} contentContainerStyle={{ paddingBottom: 40 }} showsVerticalScrollIndicator={false}>
      <ImageBackground source={photoForWorkout(workout)} style={styles.hero}>
        <LinearGradient colors={['rgba(9,9,11,0.1)', 'rgba(9,9,11,0.78)']} style={styles.shade}>
          <Pressable onPress={() => navigation.goBack()} style={[styles.back, { top: insets.top + 8 }]}>
            <Ionicons name="chevron-back" size={22} color="#fff" />
          </Pressable>
          <Text style={styles.kicker}>{day.weekday} · {day.date}</Text>
          <Text style={styles.h1}>{workout.title || 'Recovery'}</Text>
          <Text style={styles.meta}>{workout.focus} · {workout.duration_min} min</Text>
        </LinearGradient>
      </ImageBackground>

      <View style={{ padding: 20 }}>
        <FadeIn>
          <Text style={styles.h2}>Session</Text>
          {(workout.exercises || []).map((ex, i) => (
            <View key={i} style={styles.card}>
              <View style={styles.num}><Text style={styles.numText}>{String(i + 1).padStart(2, '0')}</Text></View>
              <View style={{ flex: 1 }}>
                <Text style={styles.title}>{ex.name}</Text>
                <Text style={styles.sub}>{ex.sets}{ex.notes ? ` · ${ex.notes}` : ''}</Text>
              </View>
            </View>
          ))}
        </FadeIn>

        <FadeIn delay={120}>
          <ImageBackground source={photos.nutrition} style={styles.foodHero} imageStyle={{ borderRadius: 20 }}>
            <LinearGradient colors={['transparent', 'rgba(9,9,11,0.7)']} style={styles.foodShade}>
              <Text style={styles.foodLabel}>Kitchen</Text>
              <Text style={styles.foodTitle}>Fuel for this day</Text>
            </LinearGradient>
          </ImageBackground>
          {meals.map((meal) => (
            <View key={meal.id || meal.meal_type} style={styles.cardCol}>
              <Text style={styles.mealType}>{meal.meal_type} · {meal.calories} kcal</Text>
              <Text style={styles.title}>{meal.title}</Text>
              {(meal.items || []).map((item) => (
                <Text key={item} style={styles.sub}>• {item}</Text>
              ))}
            </View>
          ))}
        </FadeIn>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  hero: { height: 280, justifyContent: 'flex-end' },
  shade: { flex: 1, justifyContent: 'flex-end', padding: 22 },
  back: { position: 'absolute', left: 16, width: 40, height: 40, borderRadius: 14, backgroundColor: 'rgba(0,0,0,0.35)', alignItems: 'center', justifyContent: 'center' },
  kicker: { color: '#ffb199', fontWeight: '800', letterSpacing: 1.2, textTransform: 'uppercase', fontSize: 11 },
  h1: { color: '#fff', fontSize: 32, fontWeight: '900', marginTop: 6 },
  meta: { color: 'rgba(255,255,255,0.86)', marginTop: 4 },
  h2: { color: colors.ink, fontSize: 20, fontWeight: '800', marginBottom: 12 },
  card: { backgroundColor: '#fff', borderRadius: 16, padding: 14, marginBottom: 8, flexDirection: 'row', gap: 12, alignItems: 'center' },
  cardCol: { backgroundColor: '#fff', borderRadius: 16, padding: 14, marginBottom: 8 },
  num: { width: 36, height: 36, borderRadius: 12, backgroundColor: colors.accentSoft, alignItems: 'center', justifyContent: 'center' },
  numText: { color: colors.accent, fontWeight: '900', fontSize: 12 },
  title: { color: colors.ink, fontWeight: '800' },
  sub: { color: colors.muted, marginTop: 4, lineHeight: 20 },
  foodHero: { height: 140, marginTop: 18, marginBottom: 12, justifyContent: 'flex-end' },
  foodShade: { flex: 1, justifyContent: 'flex-end', padding: 16, borderRadius: 20 },
  foodLabel: { color: '#ffb199', fontWeight: '800', fontSize: 11, letterSpacing: 1.2, textTransform: 'uppercase' },
  foodTitle: { color: '#fff', fontSize: 22, fontWeight: '900' },
  mealType: { color: colors.accent, fontSize: 11, fontWeight: '800', textTransform: 'uppercase', marginBottom: 4 },
});
