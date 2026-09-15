import { useRef } from 'react';
import { Animated, Pressable } from 'react-native';

export default function PressScale({ children, onPress, disabled, style }) {
  const scale = useRef(new Animated.Value(1)).current;

  function down() {
    Animated.spring(scale, { toValue: 0.97, useNativeDriver: true, speed: 40, bounciness: 0 }).start();
  }
  function up() {
    Animated.spring(scale, { toValue: 1, useNativeDriver: true, speed: 20, bounciness: 6 }).start();
  }

  return (
    <Pressable onPress={onPress} disabled={disabled} onPressIn={down} onPressOut={up}>
      <Animated.View style={[{ transform: [{ scale }] }, style]}>{children}</Animated.View>
    </Pressable>
  );
}
