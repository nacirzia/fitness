import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { colors } from '../theme';

export default function DayScreen({ route }) {
  const day = route.params?.day || {};
  const workout = day.workout || {};
  const meals = day.meals || [];

  return (
    <ScrollView style={styles.wrap} contentContainerStyle={{ padding: 20 }}>
      <Text style={styles.kicker}>{day.date} · {day.weekday}</Text>
      <Text style={styles.h1}>{workout.title || 'Recovery'}</Text>
      <Text style={styles.p}>{workout.focus} · {workout.duration_min} min</Text>

      <Text style={styles.h2}>Workout</Text>
      {(workout.exercises || []).map((ex, i) => (
        <View key={i} style={styles.card}>
          <Text style={styles.title}>{ex.name}</Text>
          <Text style={styles.meta}>{ex.sets}{ex.notes ? ` · ${ex.notes}` : ''}</Text>
        </View>
      ))}

      <Text style={styles.h2}>Diet</Text>
      {meals.map((meal) => (
        <View key={meal.id || meal.meal_type} style={styles.card}>
          <Text style={styles.mealType}>{meal.meal_type} · {meal.calories} kcal</Text>
          <Text style={styles.title}>{meal.title}</Text>
          {(meal.items || []).map((item) => (
            <Text key={item} style={styles.meta}>• {item}</Text>
          ))}
        </View>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  kicker: { color: colors.accent, fontWeight: '800' },
  h1: { color: colors.text, fontSize: 30, fontWeight: '900', marginTop: 8 },
  h2: { color: colors.text, fontSize: 18, fontWeight: '800', marginTop: 22, marginBottom: 8 },
  p: { color: colors.muted, marginTop: 6 },
  card: { backgroundColor: colors.card, borderRadius: 14, padding: 14, marginBottom: 8 },
  title: { color: colors.text, fontWeight: '700' },
  meta: { color: colors.muted, marginTop: 4 },
  mealType: { color: colors.accent, fontSize: 11, fontWeight: '800', textTransform: 'uppercase', marginBottom: 4 },
});
