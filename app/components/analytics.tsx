import Script from "next/script";

/**
 * Analytics integration point.
 *
 * No-op unless the corresponding counter IDs are provided via env vars:
 * - `NEXT_PUBLIC_YM_ID` — Yandex.Metrika counter number
 * - `NEXT_PUBLIC_GA_MEASUREMENT_ID` — Google Analytics 4 measurement ID (G-…)
 *
 * Scripts are injected asynchronously (after hydration) so they don't block
 * first paint or LCP. Until the env vars are set on the deployment, nothing
 * is rendered.
 */
export function Analytics() {
  const ymId = process.env.NEXT_PUBLIC_YM_ID?.trim();
  const gaId = process.env.NEXT_PUBLIC_GA_MEASUREMENT_ID?.trim();

  if (!ymId && !gaId) return null;

  return (
    <>
      {ymId && (
        <>
          <Script id="yandex-metrika" strategy="afterInteractive">
            {`(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})(window,document,"script","https://mc.yandex.ru/metrika/tag.js","ym");ym(${ymId},"init",{clickmap:true,trackLinks:true,accurateTrackBounce:true,webvisor:true});`}
          </Script>
          <noscript>
            <div>
              {/* eslint-disable-next-line @next/next/no-img-element -- tracking pixel, not an LCP image */}
              <img
                src={`https://mc.yandex.ru/watch/${ymId}`}
                style={{ position: "absolute", left: "-9999px" }}
                alt=""
              />
            </div>
          </noscript>
        </>
      )}
      {gaId && (
        <>
          <Script
            src={`https://www.googletagmanager.com/gtag/js?id=${gaId}`}
            strategy="afterInteractive"
          />
          <Script id="google-analytics" strategy="afterInteractive">
            {`window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","${gaId}");`}
          </Script>
        </>
      )}
    </>
  );
}
