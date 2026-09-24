# Campus Coin — Database Design & Schema Specification

## 1. Engine & Character Set
- **Database Engine:** MySQL / MariaDB (InnoDB)
- **Character Set:** `utf8mb4`
- **Collation:** `utf8mb4_unicode_ci`

---

## 2. Table Specifications

### 2.1 `users`
Primary identity and profile table for students and administrators.

| Column | Type | Nullable | Default | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | Primary Key |
| `name` | `VARCHAR(255)` | No | — | Full legal or display name |
| `email` | `VARCHAR(255)` | No | — | Unique login email (campus `.edu` encouraged) |
| `password` | `VARCHAR(255)` | No | — | Bcrypt hashed secret |
| `role` | `ENUM('student','admin')` | No | `'student'` | System authorization role |
| `status` | `ENUM('active','disabled')` | No | `'active'` | Account operational status |
| `academic_year` | `ENUM('Freshman','Sophomore','Junior','Senior','Graduate')` | Yes | `NULL` | Student cohort status |
| `monthly_allowance` | `DECIMAL(10,2)` | No | `0.00` | Baseline monthly inflow/stipend |
| `savings_goal` | `DECIMAL(10,2)` | No | `0.00` | Monthly target savings target |
| `email_verified_at` | `TIMESTAMP` | Yes | `NULL` | Verification timestamp |
| `remember_token` | `VARCHAR(100)` | Yes | `NULL` | Persistent session token |
| `created_at` | `TIMESTAMP` | Yes | `NULL` | Record creation timestamp |
| `updated_at` | `TIMESTAMP` | Yes | `NULL` | Record modification timestamp |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE KEY users_email_unique (email)`
- `INDEX users_role_index (role)`
- `INDEX users_status_index (status)`

---

### 2.2 `categories` *(Phase 1)*
Classification tags for transactions and budgets.

| Column | Type | Nullable | Default | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | Primary Key |
| `user_id` | `BIGINT UNSIGNED` | Yes | `NULL` | Foreign key to `users.id` (`NULL` = system default category) |
| `name` | `VARCHAR(100)` | No | — | Category title (e.g., Food, Allowance) |
| `type` | `ENUM('income','expense')` | No | `'expense'` | Cash flow classification |
| `icon` | `VARCHAR(50)` | No | `'tag'` | Lucide icon identifier |
| `color` | `VARCHAR(7)` | No | `'#059669'` | Hex color code |
| `is_default` | `BOOLEAN` | No | `FALSE` | Flag for global default categories |
| `is_active` | `BOOLEAN` | No | `TRUE` | Operational activation status *(Phase 7)* |
| `created_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |
| `updated_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |

**Foreign Keys & Constraints:**
- `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`
- `INDEX categories_user_id_index (user_id)`
- `INDEX categories_is_active_index (is_active)`

---

### 2.3 `transactions` *(Phase 1)*
Financial cash-flow ledger.

| Column | Type | Nullable | Default | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | Primary Key |
| `user_id` | `BIGINT UNSIGNED` | No | — | Foreign key to `users.id` |
| `category_id` | `BIGINT UNSIGNED` | No | — | Foreign key to `categories.id` |
| `type` | `ENUM('income','expense')` | No | — | Transaction type |
| `amount` | `DECIMAL(10,2)` | No | — | Exact currency value |
| `description` | `VARCHAR(255)` | No | — | Merchant or title description |
| `transaction_date` | `DATE` | No | — | Occurrence date |
| `payment_method` | `ENUM('cash','card','bank_transfer','upi','digital_wallet','other')` | No | `'card'` | Payment channel |
| `is_recurring` | `BOOLEAN` | No | `FALSE` | Recurring transaction flag |
| `ai_suggested` | `BOOLEAN` | No | `FALSE` | Categorization was suggested by AI |
| `ai_confidence` | `DECIMAL(3,2)` | Yes | `NULL` | Advisory confidence score (0.00-1.00) |
| `created_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |
| `updated_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |

---

### 2.4 `budgets` *(Phase 2 — Implemented & Verified)*
Monthly limits by student and category.

| Column | Type | Nullable | Default | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | Primary Key |
| `user_id` | `BIGINT UNSIGNED` | No | — | Foreign key to `users.id` |
| `category_id` | `BIGINT UNSIGNED` | No | — | Foreign key to `categories.id` |
| `amount` | `DECIMAL(10,2)` | No | — | Maximum planned monthly limit |
| `month_year` | `VARCHAR(7)` | No | — | Format `YYYY-MM` (e.g. `2026-09`) |
| `created_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |
| `updated_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |

**Foreign Keys & Constraints:**
- `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`
- `FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE`
- `UNIQUE KEY unique_user_category_month (user_id, category_id, month_year)`
- `INDEX budgets_user_id_month_year_index (user_id, month_year)`

---

### 2.5 `saving_tips` *(Phase 5 — Implemented & Verified)*
Personalized saving opportunities generated deterministically from student financial metrics.

| Column | Type | Nullable | Default | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | Primary Key |
| `user_id` | `BIGINT UNSIGNED` | No | — | Foreign key to `users.id` |
| `rule_key` | `VARCHAR(64)` | No | — | Rule identifier (e.g. `category_above_average`) |
| `category_id` | `BIGINT UNSIGNED` | Yes | `NULL` | Foreign key to `categories.id` (if category-specific) |
| `title` | `VARCHAR(255)` | No | — | Concise headline |
| `message` | `TEXT` | No | — | Trigger explanation and diagnostic context |
| `suggestion` | `TEXT` | No | — | Concrete actionable financial advice |
| `trigger_data` | `JSON` | Yes | `NULL` | Structured metric snapshots |
| `estimated_savings` | `DECIMAL(10,2)` | No | `0.00` | Calculated potential monthly savings impact |
| `status` | `ENUM('active','dismissed','pinned')` | No | `'active'` | Tip lifecycle state |
| `dismissed_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp when student dismissed tip |
| `pinned_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp when student pinned tip |
| `created_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |
| `updated_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |

**Foreign Keys & Constraints:**
- `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`
- `FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE`
- `UNIQUE KEY unique_user_rule_category (user_id, rule_key, category_id)`
- `INDEX saving_tips_user_id_status_index (user_id, status)`
- `INDEX saving_tips_user_id_savings_index (user_id, estimated_savings)`

---

### 2.6 `category_learnings` *(Phase 6 — Implemented & Verified)*
Student-specific categorization preferences learned from manual corrections and confirmations.

| Column | Type | Nullable | Default | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | AUTO_INCREMENT | Primary Key |
| `user_id` | `BIGINT UNSIGNED` | No | — | Student owner (`users.id`) |
| `keyword` | `VARCHAR(100)` | No | — | Normalized description keyword / merchant token |
| `category_id` | `BIGINT UNSIGNED` | No | — | Target preferred category (`categories.id`) |
| `usage_count` | `INT UNSIGNED` | No | `1` | Frequency count of this correction |
| `last_used_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp of most recent correction |
| `created_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |
| `updated_at` | `TIMESTAMP` | Yes | `NULL` | Timestamp |

**Foreign Keys & Constraints:**
- `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`
- `FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE`
- `UNIQUE KEY category_learnings_user_id_keyword_unique (user_id, keyword)`
- `INDEX category_learnings_user_id_usage_count_index (user_id, usage_count)`

---

## 3. Data Isolation Rules
1. **Never Trust Client Identifiers:** Query builders and Eloquent scopes must explicitly enforce `user_id = Auth::id()`.
2. **Deterministic Calculations:** All arithmetic aggregation runs through `SUM(amount)` with `DECIMAL(10,2)` preservation.
3. **Learned Correction Scoping:** Category learnings are strictly scoped to the student. Learned preferences of Student A never influence suggestions for Student B.
4. **Saving Tips Scoping:** Saving tips are strictly user-isolated with unique constraint `(user_id, rule_key, category_id)`. Student A cannot view, pin, or dismiss tips of Student B.
