import * as ScreenCapture from 'expo-screen-capture';

export function preventScreenCapture(): () => void {
  ScreenCapture.preventScreenCaptureAsync();
  const subscription = ScreenCapture.addScreenshotListener(() => {
    console.warn('[Security] Screenshot detected');
  });
  return () => {
    ScreenCapture.allowScreenCaptureAsync();
    subscription.remove();
  };
}
