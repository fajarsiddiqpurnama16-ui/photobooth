export type Stage = 'loading' | 'hero' | 'template' | 'camera' | 'preview';

export type Category = 'strip' | 'grid' | 'single' | 'creative';

export interface PhotoSlot {
  x: number;
  y: number;
  width: number;
  height: number;
  rotation?: number;
  borderRadius?: number;
  shape?: 'rectangle' | 'circle' | 'oval' | 'triangle' | 'diamond' | 'custom' | string;
  points?: Array<{ x: number; y: number }>;
  skewX?: number;
  skewY?: number;
}

export type DecorationType =
  | 'rect'
  | 'circle'
  | 'line'
  | 'heart'
  | 'star'
  | 'dot'
  | 'tape'
  | 'border-inset'
  | 'corner';

export interface Decoration {
  type: DecorationType;
  layer?: 'back' | 'front';
  x?: number;
  y?: number;
  width?: number;
  height?: number;
  color?: string;
  rotation?: number;
  radius?: number;
  x2?: number;
  y2?: number;
  strokeWidth?: number;
  opacity?: number;
}

export interface TemplateText {
  text: string;
  x: number;
  y: number;
  fontSize?: number;
  color?: string;
  fontFamily?: string;
  fontWeight?: string;
  rotation?: number;
  align?: 'left' | 'center' | 'right';
  letterSpacing?: number;
}

export interface TemplateBorder {
  color: string;
  width: number;
  inset?: number;
}

export interface PhotoBorder {
  color: string;
  width: number;
  padding?: number;
}

export interface TemplateBackground {
  type: 'solid' | 'gradient';
  color?: string;
  colors?: string[];
  direction?: 'vertical' | 'horizontal' | 'diagonal';
}

export interface Template {
  id: string;
  name: string;
  category: Category;
  templateImage?: string;
  width: number;
  height: number;
  photoCount: number;
  background: TemplateBackground;
  photoSlots: PhotoSlot[];
  decorations?: Decoration[];
  text?: TemplateText[];
  border?: TemplateBorder;
  photoBorder?: PhotoBorder;
  accent?: string;
}

export interface FilterDef {
  id: string;
  name: string;
  css: string;
}
