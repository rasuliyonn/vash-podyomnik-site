CREATE TABLE `equipment_documents` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`equipment_id` integer NOT NULL,
	`url` text NOT NULL,
	`name` text NOT NULL,
	`doc_type` text DEFAULT 'pdf' NOT NULL,
	`size_kb` integer
);
--> statement-breakpoint
CREATE TABLE `equipment_images` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`equipment_id` integer NOT NULL,
	`url` text NOT NULL,
	`caption` text DEFAULT '' NOT NULL,
	`image_type` text DEFAULT 'gallery' NOT NULL,
	`sort_order` integer DEFAULT 0 NOT NULL
);
--> statement-breakpoint
CREATE TABLE `equipment_types` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`slug` text NOT NULL,
	`title` text NOT NULL,
	`description` text DEFAULT '' NOT NULL,
	`features_json` text DEFAULT '[]' NOT NULL,
	`advantages_json` text DEFAULT '[]' NOT NULL,
	`applications_json` text DEFAULT '[]' NOT NULL,
	`tech_specs_json` text DEFAULT '[]' NOT NULL,
	`video_url` text,
	`sort_order` integer DEFAULT 0 NOT NULL,
	`visible` integer DEFAULT true NOT NULL,
	`created_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL
);
--> statement-breakpoint
CREATE UNIQUE INDEX `equipment_types_slug_unique` ON `equipment_types` (`slug`);--> statement-breakpoint
CREATE TABLE `form_submissions` (
	`id` integer PRIMARY KEY AUTOINCREMENT NOT NULL,
	`lift_type` text NOT NULL,
	`capacity` text NOT NULL,
	`height` text DEFAULT '' NOT NULL,
	`platform_size` text DEFAULT '' NOT NULL,
	`city` text DEFAULT '' NOT NULL,
	`stops` text NOT NULL,
	`status` text DEFAULT 'new' NOT NULL,
	`created_at` text DEFAULT CURRENT_TIMESTAMP NOT NULL
);
