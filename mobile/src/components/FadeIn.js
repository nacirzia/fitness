import { useEffect, useRef } from 'react';
import { Animated } from 'react-native';

export default function FadeIn({ children, delay = 0, style }) {
  const opacity = useRef(new Animated.Value(0)).current;
  const y = useRef(new Animated.Value(18)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(opacity, { toValue: 1, duration: 560, delay, useNativeDriver: true }),
      Animated.spring(y, { toValue: 0, friction: 9, delay, useNativeDriver: true }),
    ]).start();
  }, [delay, opacity, y]);

  return (
    <Animated.View style={[{ opacity, transform: [{ translateY: y }] }, style]}>
      {children}
    </Animated.View>
  );
}
