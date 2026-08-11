import type { MetadataRoute } from "next";
import { SITE_URL } from "../lib/site-config";

const EQUIPMENT_SLUGS = [
  "shaftnye",
  "konsolnye",
  "servisnye",
  "kottedzhnye",
  "platformy",
];

export default function sitemap(): MetadataRoute.Sitemap {
  const now = new Date();
  const monthly = "monthly" as const;
  const yearly = "yearly" as const;

  return [
    {
      url: SITE_URL,
      lastModified: now,
      changeFrequency: monthly,
      priority: 1,
    },
    ...EQUIPMENT_SLUGS.map((slug) => ({
      url: `${SITE_URL}/equipment/${slug}`,
      lastModified: now,
      changeFrequency: monthly,
      priority: 0.8,
    })),
    {
      url: `${SITE_URL}/privacy`,
      lastModified: now,
      changeFrequency: yearly,
      priority: 0.2,
    },
    {
      url: `${SITE_URL}/terms`,
      lastModified: now,
      changeFrequency: yearly,
      priority: 0.2,
    },
  ];
}
