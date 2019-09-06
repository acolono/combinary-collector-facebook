DO $$ DECLARE table_record RECORD; -- drop all tables in current schema
BEGIN
    FOR table_record IN (SELECT tablename FROM pg_tables WHERE schemaname = current_schema()) LOOP
        EXECUTE 'DROP TABLE IF EXISTS ' || quote_ident(table_record.tablename) || ' CASCADE';
    END LOOP;
END $$;

CREATE TABLE "public"."attachment" (
    "id" bigint NOT NULL,
    "target_id" text NOT NULL,
    "page_id" bigint NOT NULL,
    "description" text,
    "media_url" text NOT NULL,
    "attachment_type" text NOT NULL,
    "target_url" text NOT NULL
) WITH (oids = false);

CREATE TABLE "public"."comment" (
    "id" bigint NOT NULL,
    "post_id" bigint NOT NULL,
    "page_id" bigint NOT NULL,
    "created_time" timestamp NOT NULL,
    "message" text,
    "comment_count" integer,
    "like_count" integer,
    "commenter_id" bigint,
    "commenter_name" text,
    CONSTRAINT "comment_pk" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE TABLE "public"."post" (
    "id" bigint NOT NULL,
    "page_id" bigint NOT NULL,
    "type" text NOT NULL,
    "created_time" timestamp NOT NULL,
    "story" text,
    "message" text,
    CONSTRAINT "post_pk" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE TABLE "public"."reaction" (
    "id" BIGSERIAL NOT NULL,
    "page_id" bigint NOT NULL,
    "parent_id" bigint NOT NULL,
    "profile_id" bigint NOT NULL,
    "profile_name" text NOT NULL,
    "type" text NOT NULL,
    "created_time" text NOT NULL,
    CONSTRAINT "reaction_pk" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE TABLE "public"."json_api_call" (
    "id" BIGSERIAL NOT NULL,
    "raw" json NOT NULL,
    "page_id" bigint NOT NULL,
    CONSTRAINT "json_api_call_pk" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE TABLE "public"."json_webhook" (
    "id" BIGSERIAL NOT NULL,
    "raw" json NOT NULL,
    "page_id" bigint NOT NULL,
    CONSTRAINT "json_webhook_pk" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE TABLE "public"."description_tags" (
    "post_id" bigint NOT NULL,
    "person_id" bigint NOT NULL,
    "page_id" bigint NOT NULL,
    "person_name" text,
    "type" text,
    CONSTRAINT "description_tags_post_id_fk" FOREIGN KEY (post_id) REFERENCES post(id) NOT DEFERRABLE
) WITH (oids = false);

ALTER TABLE "public"."comment" ADD CONSTRAINT "comment_post" FOREIGN KEY (post_id) REFERENCES post(id) NOT DEFERRABLE;

