import * as Device from 'expo-device';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

export async function checkDeviceIntegrity(): Promise<{
  safe: boolean;
  reason?: string;
}> {
  // Only check on physical devices (not simulators/emulators)
  if (!Device.isDevice) {
    return { safe: true };
  }

  // Check for rooted/jailbroken indicators
  if (Platform.OS === 'android') {
    const brand = Device.brand?.toLowerCase() ?? '';
    const model = Device.modelName?.toLowerCase() ?? '';
    // Basic heuristic — enhanced detection would use a native module
    if (brand === 'unknown' && model === 'unknown') {
      return { safe: false, reason: 'unverified_device' };
    }
  }

  return { safe: true };
}

export async function enforceDeviceIntegrity(): Promise<void> {
  const result = await checkDeviceIntegrity();
  if (!result.safe) {
    // Clear sensitive data if device is compromised
    try {
      await SecureStore.deleteItemAsync('authToken');
      await SecureStore.deleteItemAsync('refreshToken');
    } catch {
      // Best effort cleanup
    }
    console.warn('[Security] Device integrity check failed:', result.reason);
  }
}
