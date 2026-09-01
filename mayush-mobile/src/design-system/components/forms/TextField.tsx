import React, { useState } from 'react';
import { TextInput, View, ViewStyle, StyleProp, TextInputProps, TouchableOpacity, StyleSheet } from 'react-native';
import { MayushText } from '../typography/MayushText';
import { MayushIcon, MayushIconName } from '../navigation/MayushIcon';
import { colors } from '../../tokens/colors';
import { useTheme } from '../../theme/useTheme';

export interface TextFieldProps extends Omit<TextInputProps, 'style'> {
  label?: string;
  error?: string;
  helperText?: string;
  containerStyle?: StyleProp<ViewStyle>;
  leftIcon?: MayushIconName;
  rightIcon?: MayushIconName;
  onRightIconPress?: () => void;
}

export const TextField: React.FC<TextFieldProps> = ({
  label,
  error,
  helperText,
  containerStyle,
  value,
  onChangeText,
  placeholder,
  editable = true,
  leftIcon,
  rightIcon,
  onRightIconPress,
  ...rest
}) => {
  const [isFocused, setIsFocused] = useState(false);
  const { isRTL } = useTheme();
  const borderColor = error ? colors.semantic.error : isFocused ? colors.brand.orange500 : colors.surface.borderWarm;

  return (
    <View style={[styles.wrapper, containerStyle]}>
      {label ? <MayushText variant="inputLabel" color={colors.brand.navy900} style={styles.label}>{label}</MayushText> : null}
      <View style={[styles.inputShell, { borderColor }, !editable && styles.disabled]}>
        {leftIcon ? <MayushIcon name={leftIcon} size={23} color={colors.neutral.gray700} /> : null}
        <TextInput
          value={value}
          onChangeText={onChangeText}
          placeholder={placeholder}
          placeholderTextColor="#8B8B8E"
          editable={editable}
          onFocus={() => setIsFocused(true)}
          onBlur={() => setIsFocused(false)}
          style={[styles.input, { textAlign: isRTL ? 'right' : 'left' }]}
          {...rest}
        />
        {rightIcon ? (
          <TouchableOpacity disabled={!onRightIconPress} onPress={onRightIconPress} hitSlop={8}>
            <MayushIcon name={rightIcon} size={23} color={colors.neutral.gray700} />
          </TouchableOpacity>
        ) : null}
      </View>
      {error ? <MayushText variant="caption" color={colors.semantic.error} style={styles.message}>{error}</MayushText> : null}
      {!error && helperText ? <MayushText variant="caption" color={colors.neutral.gray700} style={styles.message}>{helperText}</MayushText> : null}
    </View>
  );
};

const styles = StyleSheet.create({
  wrapper: { width: '100%' },
  label: { marginBottom: 6 },
  inputShell: {
    height: 56,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    backgroundColor: colors.surface.white,
    borderWidth: 1.5,
    borderRadius: 28,
    paddingHorizontal: 17,
  },
  disabled: { backgroundColor: colors.neutral.gray100 },
  input: {
    flex: 1,
    height: '100%',
    fontSize: 16,
    color: colors.brand.navy900,
    paddingVertical: 0,
  },
  message: { marginTop: 6 },
});
