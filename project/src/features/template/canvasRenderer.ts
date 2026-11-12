import type { Template, PhotoSlot } from '@/types';

export interface RenderPhoto {
  dataUrl: string;
  slot: PhotoSlot;
}

async function loadImage(src: string): Promise<HTMLImageElement> {
  return new Promise((resolve, reject) => {
    const img = new Image();
    if (!src.startsWith('data:')) {
      img.crossOrigin = 'anonymous';
    }
    img.onload = () => resolve(img);
    img.onerror = (e) => {
      console.error('Failed to load image:', src, e);
      reject(e);
    };
    img.src = src;
  });
}

function roundRectPath(
  ctx: CanvasRenderingContext2D,
  x: number,
  y: number,
  w: number,
  h: number,
  r: number,
) {
  const rr = Math.max(0, Math.min(r, w / 2, h / 2));
  if (typeof (ctx as any).roundRect === 'function') {
    ctx.beginPath();
    (ctx as any).roundRect(x, y, w, h, rr);
    return;
  }
  ctx.beginPath();
  ctx.moveTo(x + rr, y);
  ctx.arcTo(x + w, y, x + w, y + h, rr);
  ctx.arcTo(x + w, y + h, x, y + h, rr);
  ctx.arcTo(x, y + h, x, y, rr);
  ctx.arcTo(x, y, x + w, y, rr);
  ctx.closePath();
}

function fillBackground(ctx: CanvasRenderingContext2D, t: Template) {
  const { background: bg, width: W, height: H } = t;
  if (!bg) {
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, W, H);
    return;
  }
  if (bg.type === 'solid') {
    ctx.fillStyle = bg.color ?? '#ffffff';
    ctx.fillRect(0, 0, W, H);
  } else {
    const grd =
      bg.direction === 'horizontal'
        ? ctx.createLinearGradient(0, 0, W, 0)
        : bg.direction === 'diagonal'
          ? ctx.createLinearGradient(0, 0, W, H)
          : ctx.createLinearGradient(0, 0, 0, H);
    const colors = bg.colors ?? ['#ffffff', '#f5f5f5'];
    colors.forEach((c, i) => grd.addColorStop(i / Math.max(colors.length - 1, 1), c));
    ctx.fillStyle = grd;
    ctx.fillRect(0, 0, W, H);
  }
}

type DecorationDef = NonNullable<Template['decorations']>[number];
type TextDef = NonNullable<Template['text']>[number];

function drawDecoration(ctx: CanvasRenderingContext2D, d: DecorationDef, W: number, H: number) {
  if (!d) return;
  const opacity = d.opacity ?? 1;
  ctx.save();
  ctx.globalAlpha = opacity;
  if (d.rotation) {
    const cx = (d.x ?? 0) + (d.width ?? 0) / 2;
    const cy = (d.y ?? 0) + (d.height ?? 0) / 2;
    ctx.translate(cx, cy);
    ctx.rotate((d.rotation * Math.PI) / 180);
    ctx.translate(-cx, -cy);
  }
  ctx.fillStyle = d.color ?? '#000';
  ctx.strokeStyle = d.color ?? '#000';
  ctx.lineWidth = d.strokeWidth ?? 2;
  switch (d.type) {
    case 'rect':
      ctx.fillRect(d.x ?? 0, d.y ?? 0, d.width ?? 0, d.height ?? 0);
      break;
    case 'circle':
      ctx.beginPath();
      ctx.arc((d.x ?? 0) + (d.width ?? 0) / 2, (d.y ?? 0) + (d.height ?? 0) / 2, (d.radius ?? (d.width ?? 0) / 2), 0, Math.PI * 2);
      ctx.fill();
      break;
    case 'line':
      ctx.beginPath();
      ctx.moveTo(d.x ?? 0, d.y ?? 0);
      ctx.lineTo(d.x2 ?? 0, d.y2 ?? 0);
      ctx.stroke();
      break;
    case 'dot':
      ctx.beginPath();
      ctx.arc(d.x ?? 0, d.y ?? 0, d.radius ?? 3, 0, Math.PI * 2);
      ctx.fill();
      break;
    case 'tape': {
      ctx.fillStyle = d.color ?? 'rgba(200,200,200,0.5)';
      ctx.fillRect(d.x ?? 0, d.y ?? 0, d.width ?? 60, d.height ?? 20);
      break;
    }
    case 'border-inset': {
      ctx.strokeStyle = d.color ?? '#000';
      ctx.lineWidth = d.strokeWidth ?? 2;
      const ins = d.width ?? 8;
      ctx.strokeRect(ins, ins, W - ins * 2, H - ins * 2);
      break;
    }
    case 'corner': {
      const len = d.width ?? 20;
      const x = d.x ?? 0;
      const y = d.y ?? 0;
      ctx.lineWidth = d.strokeWidth ?? 3;
      ctx.beginPath();
      ctx.moveTo(x, y + len);
      ctx.lineTo(x, y);
      ctx.lineTo(x + len, y);
      ctx.stroke();
      break;
    }
    case 'heart': {
      const cx = (d.x ?? 0) + (d.width ?? 20) / 2;
      const cy = (d.y ?? 0) + (d.height ?? 20) / 2;
      const s = (d.width ?? 20) / 2;
      ctx.beginPath();
      ctx.moveTo(cx, cy + s * 0.8);
      ctx.bezierCurveTo(cx - s * 1.2, cy - s * 0.3, cx - s * 0.5, cy - s, cx, cy - s * 0.3);
      ctx.bezierCurveTo(cx + s * 0.5, cy - s, cx + s * 1.2, cy - s * 0.3, cx, cy + s * 0.8);
      ctx.fill();
      break;
    }
    case 'star': {
      const cx = (d.x ?? 0) + (d.width ?? 20) / 2;
      const cy = (d.y ?? 0) + (d.height ?? 20) / 2;
      const r = (d.width ?? 20) / 2;
      ctx.beginPath();
      for (let i = 0; i < 10; i++) {
        const angle = (i * Math.PI) / 5 - Math.PI / 2;
        const rad = i % 2 === 0 ? r : r * 0.45;
        const px = cx + Math.cos(angle) * rad;
        const py = cy + Math.sin(angle) * rad;
        if (i === 0) ctx.moveTo(px, py);
        else ctx.lineTo(px, py);
      }
      ctx.closePath();
      ctx.fill();
      break;
    }
  }
  ctx.restore();
}

function drawText(ctx: CanvasRenderingContext2D, t: TextDef) {
  if (!t) return;
  ctx.save();
  ctx.fillStyle = t.color ?? '#000';
  ctx.font = `${t.fontWeight ?? 'normal'} ${t.fontSize ?? 20}px ${t.fontFamily ?? 'sans-serif'}`;
  ctx.textAlign = t.align ?? 'center';
  ctx.textBaseline = 'middle';
  if (t.rotation) {
    ctx.translate(t.x, t.y);
    ctx.rotate((t.rotation * Math.PI) / 180);
    if (t.letterSpacing) {
      (ctx as any).letterSpacing = `${t.letterSpacing}px`;
    }
    ctx.fillText(t.text, 0, 0);
  } else {
    if (t.letterSpacing) {
      (ctx as any).letterSpacing = `${t.letterSpacing}px`;
    }
    ctx.fillText(t.text, t.x, t.y);
  }
  ctx.restore();
}

function drawPhotoInSlot(ctx: CanvasRenderingContext2D, img: HTMLImageElement, slot: PhotoSlot, pb: Template['photoBorder']) {
  const padding = pb?.padding ?? 0;
  const bx = slot.x + padding;
  const by = slot.y + padding;
  const bw = slot.width - padding * 2;
  const bh = slot.height - padding * 2;

  ctx.save();
  if (slot.rotation) {
    const cx = slot.x + slot.width / 2;
    const cy = slot.y + slot.height / 2;
    ctx.translate(cx, cy);
    ctx.rotate((slot.rotation * Math.PI) / 180);
    ctx.translate(-cx, -cy);
  }

  const shape = slot.shape || 'rectangle';

  // Helper to trace shape path
  const traceShapePath = (targetX: number, targetY: number, targetW: number, targetH: number, r: number) => {
    ctx.beginPath();
    switch (shape) {
      case 'circle': {
        const rad = Math.min(targetW, targetH) / 2;
        ctx.arc(targetX + targetW / 2, targetY + targetH / 2, rad, 0, Math.PI * 2);
        break;
      }
      case 'oval': {
        ctx.ellipse(targetX + targetW / 2, targetY + targetH / 2, targetW / 2, targetH / 2, 0, 0, Math.PI * 2);
        break;
      }
      case 'triangle': {
        ctx.moveTo(targetX + targetW / 2, targetY);
        ctx.lineTo(targetX + targetW, targetY + targetH);
        ctx.lineTo(targetX, targetY + targetH);
        ctx.closePath();
        break;
      }
      case 'diamond': {
        ctx.moveTo(targetX + targetW / 2, targetY);
        ctx.lineTo(targetX + targetW, targetY + targetH / 2);
        ctx.lineTo(targetX + targetW / 2, targetY + targetH);
        ctx.lineTo(targetX, targetY + targetH / 2);
        ctx.closePath();
        break;
      }
      case 'custom': {
        if (slot.points && slot.points.length >= 3) {
          slot.points.forEach((pt, pIdx) => {
            const px = targetX + (pt.x / 100) * targetW;
            const py = targetY + (pt.y / 100) * targetH;
            if (pIdx === 0) ctx.moveTo(px, py);
            else ctx.lineTo(px, py);
          });
          ctx.closePath();
        } else {
          roundRectPath(ctx, targetX, targetY, targetW, targetH, r);
        }
        break;
      }
      case 'heart': {
        const cx = targetX + targetW / 2;
        const cy = targetY + targetH / 2;
        const s = Math.min(targetW, targetH) / 2;
        ctx.moveTo(cx, cy + s * 0.8);
        ctx.bezierCurveTo(cx - s * 1.2, cy - s * 0.3, cx - s * 0.5, cy - s, cx, cy - s * 0.3);
        ctx.bezierCurveTo(cx + s * 0.5, cy - s, cx + s * 1.2, cy - s * 0.3, cx, cy + s * 0.8);
        ctx.closePath();
        break;
      }
      case 'star': {
        const cx = targetX + targetW / 2;
        const cy = targetY + targetH / 2;
        const rad = Math.min(targetW, targetH) / 2;
        for (let i = 0; i < 10; i++) {
          const angle = (i * Math.PI) / 5 - Math.PI / 2;
          const dist = i % 2 === 0 ? rad : rad * 0.45;
          const px = cx + Math.cos(angle) * dist;
          const py = cy + Math.sin(angle) * dist;
          if (i === 0) ctx.moveTo(px, py);
          else ctx.lineTo(px, py);
        }
        ctx.closePath();
        break;
      }
      case 'arch': {
        const rr = Math.min(targetW / 2, targetH / 2);
        ctx.moveTo(targetX, targetY + targetH);
        ctx.lineTo(targetX, targetY + rr);
        ctx.arcTo(targetX, targetY, targetX + rr, targetY, rr);
        ctx.arcTo(targetX + targetW, targetY, targetX + targetW, targetY + rr, rr);
        ctx.lineTo(targetX + targetW, targetY + targetH);
        ctx.closePath();
        break;
      }
      case 'rectangle':
      case 'rounded':
      default: {
        roundRectPath(ctx, targetX, targetY, targetW, targetH, r);
        break;
      }
    }
  };

  // photo border/background
  if (pb && pb.width > 0) {
    ctx.fillStyle = pb.color;
    traceShapePath(slot.x, slot.y, slot.width, slot.height, slot.borderRadius ?? 0);
    ctx.fill();
  }

  // Clip and draw image (cover)
  const innerRadius = (slot.borderRadius ?? 0) - Math.min(padding, (slot.borderRadius ?? 0) / 2);
  traceShapePath(bx, by, bw, bh, innerRadius);
  ctx.clip();

  const ar = img.width / img.height;
  const sar = bw / bh;
  let dw: number, dh: number, dx: number, dy: number;
  if (ar > sar) {
    dh = bh;
    dw = bh * ar;
    dx = bx + (bw - dw) / 2;
    dy = by;
  } else {
    dw = bw;
    dh = bw / ar;
    dx = bx;
    dy = by + (bh - dh) / 2;
  }
  ctx.drawImage(img, dx, dy, dw, dh);
  ctx.restore();
}

function drawBorder(ctx: CanvasRenderingContext2D, t: Template) {
  if (!t.border) return;
  ctx.save();
  ctx.strokeStyle = t.border.color;
  ctx.lineWidth = t.border.width;
  const ins = t.border.inset ?? 0;
  ctx.strokeRect(ins, ins, t.width - ins * 2, t.height - ins * 2);
  ctx.restore();
}

function isSlotAreaTransparent(img: HTMLImageElement, slots: PhotoSlot[], tWidth: number, tHeight: number): boolean {
  try {
    const testCanvas = document.createElement('canvas');
    testCanvas.width = 1;
    testCanvas.height = 1;
    const testCtx = testCanvas.getContext('2d', { willReadFrequently: true });
    if (!testCtx) return false;

    const nw = img.naturalWidth || tWidth;
    const nh = img.naturalHeight || tHeight;

    for (const slot of slots) {
      const imgX = Math.floor(((slot.x + slot.width / 2) / tWidth) * nw);
      const imgY = Math.floor(((slot.y + slot.height / 2) / tHeight) * nh);

      testCtx.clearRect(0, 0, 1, 1);
      testCtx.drawImage(img, imgX, imgY, 1, 1, 0, 0, 1, 1);
      const pixel = testCtx.getImageData(0, 0, 1, 1).data;
      if (pixel[3] < 128) {
        return true;
      }
    }
    return false;
  } catch (err) {
    console.warn('Could not test slot transparency:', err);
    return false;
  }
}

export async function renderTemplate(
  template: Template,
  photoDataUrls: string[],
  outputScale = 2,
): Promise<string> {
  const canvas = document.createElement('canvas');
  canvas.width = template.width * outputScale;
  canvas.height = template.height * outputScale;
  const ctx = canvas.getContext('2d')!;
  ctx.scale(outputScale, outputScale);
  ctx.imageSmoothingEnabled = true;
  ctx.imageSmoothingQuality = 'high';

  // 1. Base Background (solid or gradient)
  fillBackground(ctx, template);

  // 2. LAYER DASAR: DESAIN TEMPLATE ASLI (misal koran atau background frame)
  let templateImgEl: HTMLImageElement | null = null;
  if (template.templateImage) {
    try {
      templateImgEl = await loadImage(template.templateImage);
      ctx.drawImage(templateImgEl, 0, 0, template.width, template.height);
    } catch (err) {
      console.warn('Could not draw template background image:', err);
    }
  }

  // 3. Back decorations (if legacy template)
  template.decorations?.filter((d) => (d.layer ?? 'back') === 'back').forEach((d) => drawDecoration(ctx, d, template.width, template.height));

  // 4. FOTO CUSTOMER: Ditempatkan di atas slot foto
  const imgs = await Promise.all(photoDataUrls.slice(0, template.photoSlots.length).map(loadImage));
  for (let i = 0; i < imgs.length && i < template.photoSlots.length; i++) {
    drawPhotoInSlot(ctx, imgs[i], template.photoSlots[i], template.photoBorder);
  }

  // 5. LAYER ATAS: OVERLAY FRAME
  // Hanya jika desain template memiliki cutout transparan pada area slot foto
  if (templateImgEl && template.photoSlots && template.photoSlots.length > 0) {
    if (isSlotAreaTransparent(templateImgEl, template.photoSlots, template.width, template.height)) {
      ctx.drawImage(templateImgEl, 0, 0, template.width, template.height);
    }
  }

  // 6. Border / frame (if legacy template)
  drawBorder(ctx, template);

  // 7. Front decorations (if legacy template)
  template.decorations?.filter((d) => d.layer === 'front').forEach((d) => drawDecoration(ctx, d, template.width, template.height));

  // 8. Text (if legacy template)
  template.text?.forEach((t) => drawText(ctx, t));

  return canvas.toDataURL('image/png');
}