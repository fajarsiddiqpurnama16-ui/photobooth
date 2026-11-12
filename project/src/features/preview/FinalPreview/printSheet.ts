const loadImage = (src: string) =>
  new Promise<HTMLImageElement>((res, rej) => {
    const img = new Image();
    img.onload = () => res(img);
    img.onerror = rej;
    img.src = src;
  });

export async function buildPrintSheet(dataUrl: string): Promise<string> {
  const img = await loadImage(dataUrl);
  const W = 1200, H = 1800; // 4R, 300 dpi
  const c = document.createElement('canvas');
  c.width = W;
  c.height = H;
  const ctx = c.getContext('2d')!;
  ctx.fillStyle = '#fff';
  ctx.fillRect(0, 0, W, H);

  const isStrip = img.height / img.width > 1.6;
  const cols = isStrip ? 2 : 1;
  const pad = 50;
  const cellW = W / cols;

  for (let i = 0; i < cols; i++) {
    const s = Math.min((cellW - pad * 2) / img.width, (H - pad * 2) / img.height);
    const w = img.width * s;
    const h = img.height * s;
    ctx.drawImage(img, i * cellW + (cellW - w) / 2, (H - h) / 2, w, h);
  }

  if (isStrip) {
    ctx.setLineDash([12, 12]);
    ctx.strokeStyle = '#bbb';
    ctx.beginPath();
    ctx.moveTo(W / 2, 0);
    ctx.lineTo(W / 2, H);
    ctx.stroke();
  }
  return c.toDataURL('image/png');
}

export function printSheet(sheetUrl: string) {
  const iframe = document.createElement('iframe');
  iframe.style.cssText = 'position:fixed;width:0;height:0;border:0;';
  document.body.appendChild(iframe);

  const doc = iframe.contentDocument!;
  doc.open();
  doc.write(`<html><head><style>
    @page{size:4in 6in;margin:0} html,body{margin:0}
    img{width:4in;height:6in;display:block}
  </style></head><body><img id="p" src="${sheetUrl}"></body></html>`);
  doc.close();

  const img = doc.getElementById('p') as HTMLImageElement;
  img.onload = () => {
    iframe.contentWindow!.focus();
    iframe.contentWindow!.print();
    setTimeout(() => iframe.remove(), 60000);
  };
}