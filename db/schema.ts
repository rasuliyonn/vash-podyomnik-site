import { sql } from "drizzle-orm";
import { integer, sqliteTable, text } from "drizzle-orm/sqlite-core";

export const equipmentTypes = sqliteTable("equipment_types", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  slug: text("slug").notNull().unique(),
  title: text("title").notNull(),
  description: text("description").notNull().default(""),
  featuresJson: text("features_json").notNull().default("[]"),
  advantagesJson: text("advantages_json").notNull().default("[]"),
  applicationsJson: text("applications_json").notNull().default("[]"),
  techSpecsJson: text("tech_specs_json").notNull().default("[]"),
  videoUrl: text("video_url"),
  sortOrder: integer("sort_order").notNull().default(0),
  visible: integer("visible", { mode: "boolean" }).notNull().default(true),
  createdAt: text("created_at").notNull().default(sql`CURRENT_TIMESTAMP`),
});

export const equipmentImages = sqliteTable("equipment_images", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  equipmentId: integer("equipment_id").notNull(),
  url: text("url").notNull(),
  caption: text("caption").notNull().default(""),
  imageType: text("image_type").notNull().default("gallery"), // gallery | installation | project
  sortOrder: integer("sort_order").notNull().default(0),
});

export const equipmentDocuments = sqliteTable("equipment_documents", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  equipmentId: integer("equipment_id").notNull(),
  url: text("url").notNull(),
  name: text("name").notNull(),
  docType: text("doc_type").notNull().default("pdf"), // pdf | certificate | instruction | scheme | video
  sizeKb: integer("size_kb"),
});

export const formSubmissions = sqliteTable("form_submissions", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  name: text("name").notNull().default(""),
  phone: text("phone").notNull().default(""),
  email: text("email").notNull().default(""),
  liftType: text("lift_type").notNull(),
  capacity: text("capacity").notNull(),
  height: text("height").notNull().default(""),
  platformSize: text("platform_size").notNull().default(""),
  city: text("city").notNull().default(""),
  stops: text("stops").notNull(),
  status: text("status").notNull().default("new"), // new | viewed | done
  createdAt: text("created_at").notNull().default(sql`CURRENT_TIMESTAMP`),
});

export const siteContent = sqliteTable("site_content", {
  key: text("key").primaryKey(),
  value: text("value").notNull().default("{}"),
  updatedAt: text("updated_at").notNull().default(sql`CURRENT_TIMESTAMP`),
});

export const worksImages = sqliteTable("works_images", {
  id: integer("id").primaryKey({ autoIncrement: true }),
  url: text("url").notNull(),
  imageData: text("image_data"), // base64 data URL for DB-stored uploads
  caption: text("caption").notNull().default(""),
  sortOrder: integer("sort_order").notNull().default(0),
  createdAt: text("created_at").notNull().default(sql`CURRENT_TIMESTAMP`),
});
