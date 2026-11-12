import { useEffect } from 'react';

export function LoadingScreen({ onDone }: { onDone: () => void }) {
  useEffect(() => {
    onDone();
  }, [onDone]);

  return null;
}