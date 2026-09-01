/**
 * Mayush Design System - Sizing Tokens
 */

export const sizing = {
  touchMin: 44,
  buttonHeight: 48,
  inputHeight: 48,
  iconSm: 16,
  iconMd: 24,
  iconLg: 32,
  avatarSm: 32,
  avatarMd: 44,
  avatarLg: 64,
  tabBarHeight: 64,
} as const;

export type SizingToken = typeof sizing;
