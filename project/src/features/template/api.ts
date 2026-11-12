import type { Template } from '@/types';

export async function fetchTemplates(): Promise<Template[]> {
  const host = typeof window !== 'undefined' ? (window.location.hostname || 'localhost') : 'localhost';
  const port = typeof window !== 'undefined' && window.location.port ? (':' + window.location.port) : '';
  const proto = typeof window !== 'undefined' ? window.location.protocol : 'http:';
  const API_URL = 'http://localhost/photobooth/backend/index.php/Templates';

  const res = await fetch(API_URL);
  const json = await res.json();

  if (!json.status) {
    throw new Error(json.message || 'Failed to fetch templates');
  }

  const data = (json.data as Template[]).map((t) => {
  if (t.templateImage) {
    try {
      const u = new URL(t.templateImage);
      u.hostname = host;
      // port dihapus, biarkan ikut port asli dari backend (biasanya 80/default)
      t.templateImage = u.toString();
    } catch {
      // ignore
    }
  }
  return t;
});

  return data;
}
