import type { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    return <img src="/nusalens-logo.png" alt="NusaLens" width={40} height={40} {...props} />;
}
