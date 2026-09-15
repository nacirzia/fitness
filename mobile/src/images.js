export const photos = {
  hero: require('../assets/images/hero.jpg'),
  trainer: require('../assets/images/trainer.jpg'),
  weights: require('../assets/images/weights.jpg'),
  cardio: require('../assets/images/cardio.jpg'),
  yoga: require('../assets/images/yoga.jpg'),
  nutrition: require('../assets/images/nutrition.jpg'),
  woman: require('../assets/images/woman.jpg'),
  studio: require('../assets/images/studio.jpg'),
  logo: require('../assets/images/uptownlogo.png'),
};

export function photoForWorkout(workout) {
  const t = `${workout?.title || ''} ${workout?.focus || ''}`.toLowerCase();
  if (/cardio|run|hiit|endurance/.test(t)) return photos.cardio;
  if (/yoga|mobility|stretch|recover/.test(t)) return photos.yoga;
  if (/strength|kraft|weight|lift|push|pull/.test(t)) return photos.weights;
  if (/rest|off/.test(t)) return photos.woman;
  return photos.studio;
}
