CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "user_type" varchar check("user_type" in('user', 'agency')) not null default 'user',
  "profile_picture" varchar,
  "bio" varchar,
  "phone" varchar,
  "location" varchar,
  "preferences" text,
  "last_login_at" datetime,
  "last_login_ip" varchar,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "is_premium" tinyint(1) not null default '0',
  "premium_until" datetime,
  "agency_name" varchar
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "trip_moods"(
  "id" integer primary key autoincrement not null,
  "label" varchar not null,
  "label_normalized" varchar not null,
  "use_count" integer not null default '0',
  "created_by" integer,
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("created_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "trip_moods_label_unique" on "trip_moods"("label");
CREATE UNIQUE INDEX "trip_moods_label_normalized_unique" on "trip_moods"(
  "label_normalized"
);
CREATE TABLE IF NOT EXISTS "trips"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "title" varchar,
  "destination" varchar not null,
  "country" varchar,
  "mood" varchar,
  "feeling_note" text,
  "budget" varchar,
  "duration" varchar,
  "companion" varchar,
  "region" varchar,
  "accommodation" varchar,
  "origin" varchar,
  "month" varchar,
  "estimated_cost" numeric,
  "status" varchar not null default 'planned',
  "start_date" date,
  "end_date" date,
  "notes" text,
  "travel_tip" varchar,
  "visa_info" varchar,
  "flight_info" varchar,
  "best_time_to_visit" varchar,
  "is_good_right_now" tinyint(1) not null default '0',
  "top_activities" text,
  "daily_itinerary" text,
  "cost_breakdown" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "accommodations"(
  "id" integer primary key autoincrement not null,
  "geoapify_id" varchar,
  "name" varchar not null,
  "city" varchar not null,
  "country" varchar,
  "style" varchar not null default 'hotel',
  "budget_tier" varchar not null default 'mid',
  "nightly_rate" numeric not null default '0',
  "rating" numeric,
  "review_count" integer,
  "lat" numeric,
  "lng" numeric,
  "description" text,
  "image_url" varchar,
  "amenities" text,
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "accommodations_geoapify_id_unique" on "accommodations"(
  "geoapify_id"
);
CREATE TABLE IF NOT EXISTS "accommodation_searches"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "query" varchar not null,
  "style" varchar,
  "budget_tier" varchar,
  "results_count" integer not null default '0',
  "ip_address" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "coupons"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "type" varchar check("type" in('percent', 'fixed')) not null default 'percent',
  "value" numeric not null,
  "min_order" numeric not null default '0',
  "max_discount" numeric,
  "uses_total" integer not null default '0',
  "uses_limit" integer,
  "uses_per_user" integer not null default '1',
  "is_active" tinyint(1) not null default '1',
  "expires_at" datetime,
  "description" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "coupons_code_unique" on "coupons"("code");
CREATE TABLE IF NOT EXISTS "coupon_uses"(
  "id" integer primary key autoincrement not null,
  "coupon_id" integer not null,
  "user_id" integer not null,
  "booking_id" integer not null,
  "discount_amount" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("coupon_id") references "coupons"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("booking_id") references "bookings"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "bookings"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "trip_id" integer,
  "booking_reference" varchar not null,
  "seats_booked" integer not null default '1',
  "subtotal" numeric,
  "discount_amount" numeric not null default '0',
  "service_fee" numeric not null default '0',
  "total_price" numeric not null default '0',
  "coupon_code" varchar,
  "status" varchar check("status" in('pending', 'confirmed', 'cancelled', 'completed')) not null default 'confirmed',
  "passenger_details" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("trip_id") references "trips"("id") on delete set null
);
CREATE UNIQUE INDEX "bookings_booking_reference_unique" on "bookings"(
  "booking_reference"
);
CREATE TABLE IF NOT EXISTS "revenue_records"(
  "id" integer primary key autoincrement not null,
  "booking_id" integer not null,
  "user_id" integer not null,
  "booking_subtotal" numeric not null,
  "discount_amount" numeric not null default '0',
  "service_fee" numeric not null default '0',
  "agency_commission" numeric not null default '0',
  "net_revenue" numeric not null,
  "coupon_code" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("booking_id") references "bookings"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "destinations"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "country" varchar not null,
  "region" varchar not null,
  "description" text not null,
  "image_url" varchar not null,
  "price_from" numeric not null,
  "tags" text,
  "is_featured" tinyint(1) not null default '0',
  "is_editors_choice" tinyint(1) not null default '0',
  "display_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  "source" varchar,
  "source_id" varchar,
  "country_code" varchar,
  "lat" numeric,
  "lng" numeric,
  "raw_data" text
);
CREATE INDEX "destinations_is_active_is_featured_display_order_index" on "destinations"(
  "is_active",
  "is_featured",
  "display_order"
);
CREATE INDEX "destinations_source_source_id_index" on "destinations"(
  "source",
  "source_id"
);
CREATE INDEX "destinations_country_code_index" on "destinations"(
  "country_code"
);
CREATE TABLE IF NOT EXISTS "flight_searches"(
  "id" integer primary key autoincrement not null,
  "user_id" integer,
  "from_query" varchar not null,
  "to_query" varchar not null,
  "from_code" varchar,
  "to_code" varchar,
  "departure_date" date not null,
  "return_date" date,
  "adults" integer not null default '1',
  "travel_class" varchar not null default 'ECONOMY',
  "results_count" integer not null default '0',
  "ip_address" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "flight_searches_user_id_created_at_index" on "flight_searches"(
  "user_id",
  "created_at"
);
CREATE TABLE IF NOT EXISTS "mood_categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" varchar,
  "icon" varchar not null default 'compass',
  "gradient_from" varchar not null default '#e3f2fd',
  "gradient_to" varchar not null default '#bbdefb',
  "color" varchar not null default '#1976d2',
  "display_order" integer not null default '0',
  "is_active" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "mood_categories_name_unique" on "mood_categories"("name");
CREATE UNIQUE INDEX "mood_categories_slug_unique" on "mood_categories"("slug");

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_01_01_000010_create_core_tables',1);
INSERT INTO migrations VALUES(5,'2026_05_17_134728_create_destinations_table',1);
INSERT INTO migrations VALUES(6,'2026_05_18_000000_add_api_place_fields_to_destinations_table',2);
INSERT INTO migrations VALUES(7,'2026_05_18_100000_create_flight_searches_table',3);
INSERT INTO migrations VALUES(8,'2026_05_18_100001_create_mood_categories_table',3);
