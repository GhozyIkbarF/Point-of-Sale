import { clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs) {
  return twMerge(clsx(inputs))
}
export const PriceFormatter = (value) => {
  if (value === null || value === undefined) {
    return '-'
  }
  return `Rp ${Number(value).toLocaleString('id-ID')}`
}