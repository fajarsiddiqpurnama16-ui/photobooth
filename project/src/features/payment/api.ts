const API_BASE = 'http://localhost/photobooth/backend/index.php';

export interface PaymentSettings {
  qris_image: string | null;
  price: number;
  session_minutes: number;
}

export async function fetchPaymentSettings(): Promise<PaymentSettings> {
  const res = await fetch(`${API_BASE}/Payments/settings`);
  const json = await res.json();
  return {
    qris_image: json.qris_image,
    price: json.price,
    session_minutes: json.session_minutes,
  };
}

export async function createPaymentRequest(): Promise<string> {
  const res = await fetch(`${API_BASE}/Payments/create`, { method: 'POST' });
  const json = await res.json();
  return json.code;
}

export async function checkPaymentStatus(code: string) {
  const res = await fetch(`${API_BASE}/Payments/status/${code}`);
  const json = await res.json();
  return json;
}