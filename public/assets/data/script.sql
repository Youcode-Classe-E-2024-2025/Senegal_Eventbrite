-- Create the database
CREATE DATABASE eventbrite_db;

-- Connect to the database
\c eventbrite_db;

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    role VARCHAR(20) NOT NULL CHECK (role IN ('user', 'admin')),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    avatar_url VARCHAR(255)
);

-- Events table
CREATE TABLE events (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist_name VARCHAR(255) NOT NULL,
    category VARCHAR(50),
    tags TEXT[],
    date_start TIMESTAMP NOT NULL,
    date_end TIMESTAMP NOT NULL,
    location VARCHAR(255),
    price DECIMAL(10, 2) CHECK (price >= 0),
    capacity INT CHECK (capacity > 0),
    organizer_id INT REFERENCES users(id) ON DELETE CASCADE,
    status VARCHAR(20) NOT NULL CHECK (status IN ('FULL', 'EXPIRED', 'ACTIVE')),
    isActif BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT chk_date_consistency CHECK (date_start < date_end)
);

CREATE TABLE reservations (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    event_id INT REFERENCES events(id) ON DELETE CASCADE,
    ticket_id INT REFERENCES tickets(id) ON DELETE SET NULL, 
    quantity INT CHECK (quantity > 0),
    total_price DECIMAL(10, 2) CHECK (total_price >= 0),
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    qr_code TEXT,
    status VARCHAR(20) NOT NULL CHECK (status IN ('reserved', 'canceled', 'failed', 'paid')),
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    vip_options JSONB NOT NULL DEFAULT '{}'::jsonb
);

CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    reservation_id INT REFERENCES reservations(id) ON DELETE CASCADE,
    payment_method VARCHAR(50) NOT NULL, -- Méthode de paiement (ex: "stripe", "paypal")
    payment_gateway VARCHAR(50), -- Optionnel : Gateway utilisée
    transaction_id VARCHAR(100),
    amount DECIMAL(10, 2) CHECK (amount >= 0),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL CHECK (status IN ('success', 'pending', 'failed'))
);

CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    event_id INT REFERENCES events(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT, -- Description du billet
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    type VARCHAR(50) NOT NULL CHECK (type IN ('VIP', 'Premium', 'Standard'))
);

CREATE TABLE vip_options (
    id SERIAL PRIMARY KEY, -- Utiliser SERIAL au lieu de AUTO_INCREMENT
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL
);

-- Event statistics table
CREATE TABLE event_statistics (
    id SERIAL PRIMARY KEY,
    event_id INT REFERENCES events(id) ON DELETE CASCADE,
    tickets_sold INT DEFAULT 0,
    revenue DECIMAL(10, 2) DEFAULT 0.00,
    participants_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Promo codes table
CREATE TABLE promo_codes (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount DECIMAL(5, 2) CHECK (discount >= 0 AND discount <= 100),
    expiration_date TIMESTAMP NOT NULL,
    max_uses INT CHECK (max_uses > 0),
    used_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notifications table
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) NOT NULL CHECK (status IN ('read', 'not_read', 'archived')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Comments table
CREATE TABLE comments (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    event_id INT REFERENCES events(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index for performance optimization
CREATE INDEX idx_events_date_start ON events(date_start);
CREATE INDEX idx_reservations_user_event ON reservations(user_id, event_id);
CREATE INDEX idx_payments_reservation_id ON payments(reservation_id);
CREATE INDEX idx_comments_event_id ON comments(event_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_reservations_status ON reservations(status);