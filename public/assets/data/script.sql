-- Active: 1738833309622@@127.0.0.1@5432@eventbrite_db
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
    description TEXT,
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
    image_url VARCHAR(255)
);

ALTER TABLE events ADD CONSTRAINT chk_date_consistency CHECK (date_start < date_end);

-- Reservations table
CREATE TABLE reservations (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    event_id INT REFERENCES events(id) ON DELETE CASCADE,
    ticket_type VARCHAR(50) NOT NULL CHECK (ticket_type IN ('free', 'paid', 'VIP', 'early_bird')),
    quantity INT CHECK (quantity > 0),
    total_price DECIMAL(10, 2) CHECK (total_price >= 0),
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    qr_code TEXT,
    status VARCHAR(20) NOT NULL CHECK (status IN ('reserved', 'canceled', 'failed')),
    UNIQUE(user_id, event_id)
);

-- Payments table
CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    reservation_id INT REFERENCES reservations(id) ON DELETE CASCADE,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    amount DECIMAL(10, 2) CHECK (amount >= 0),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL CHECK (status IN ('success', 'pending', 'failed'))
);

-- Event statistics table
CREATE TABLE event_statistics (
    id SERIAL PRIMARY KEY,
    event_id INT REFERENCES events(id) ON DELETE CASCADE,
    tickets_sold INT DEFAULT 0,
    revenue DECIMAL(10, 2) DEFAULT 0.00,
    participants_count INT DEFAULT 0
);

-- Promo codes table
CREATE TABLE promo_codes (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount DECIMAL(5, 2) CHECK (discount >= 0 AND discount <= 100),
    expiration_date TIMESTAMP NOT NULL,
    max_uses INT CHECK (max_uses > 0),
    used_count INT DEFAULT 0
);

-- Notifications table
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) NOT NULL CHECK (status IN ('read', 'not_read', 'archived'))
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

-- Ajouter des utilisateurs
INSERT INTO users (role, email, password, name, avatar_url) VALUES
('admin', 'a@e.com', '$2a$12$pEJ7apZihAAc7MiXRpfnleKELAY7CPumM6YwluK4sgqD3k2eGIMN2', 'Bob', 'https://example.com/avatar2.jpg'),
('user', 'u1@e.com', '$2a$12$pEJ7apZihAAc7MiXRpfnleKELAY7CPumM6YwluK4sgqD3k2eGIMN2', 'Alice', 'https://example.com/avatar1.jpg'),
('user', 'u2@e.com', '$2a$12$pEJ7apZihAAc7MiXRpfnleKELAY7CPumM6YwluK4sgqD3k2eGIMN2', 'Charlie', 'https://example.com/avatar3.jpg');

-- Ajouter des événements
INSERT INTO events (title, description, category, tags, date_start, date_end, location, price, capacity, organizer_id, status, isActif, image_url) VALUES
('Tech Conference 2025', 'Une conférence sur la technologie', 'Tech', ARRAY['innovation', 'AI'], '2025-06-10 09:00:00', '2025-06-10 17:00:00', 'Paris', 50.00, 200, 2, 'ACTIVE', TRUE, 'https://example.com/event1.jpg'),
('Music Festival', 'Un festival de musique incroyable', 'Music', ARRAY['rock', 'live'], '2025-07-20 18:00:00', '2025-07-21 02:00:00', 'Marseille', 30.00, 500, 2, 'ACTIVE', TRUE, 'https://example.com/event2.jpg');

-- Ajouter des réservations
INSERT INTO reservations (user_id, event_id, ticket_type, quantity, total_price, qr_code, status) VALUES
(1, 1, 'paid', 2, 100.00, 'QR123ABC', 'reserved'),
(3, 2, 'VIP', 1, 60.00, 'QR456DEF', 'reserved');

-- Ajouter des paiements
INSERT INTO payments (reservation_id, payment_method, transaction_id, amount, status) VALUES
(1, 'credit_card', 'TXN789XYZ', 100.00, 'success'),
(2, 'paypal', 'TXN654LMN', 60.00, 'success');

-- Ajouter des statistiques d'événements
INSERT INTO event_statistics (event_id, tickets_sold, revenue, participants_count) VALUES
(1, 50, 2500.00, 50),
(2, 100, 3000.00, 100);

-- Ajouter des codes promo
INSERT INTO promo_codes (code, discount, expiration_date, max_uses, used_count) VALUES
('WELCOME10', 10.00, '2025-12-31 23:59:59', 100, 10),
('SUMMER20', 20.00, '2025-07-30 23:59:59', 50, 5);

-- Ajouter des notifications
INSERT INTO notifications (user_id, message, is_read, status) VALUES
(1, 'Votre réservation a été confirmée.', FALSE, 'not_read'),
(3, 'Votre paiement a été accepté.', TRUE, 'read');

-- Ajouter des commentaires
INSERT INTO comments (user_id, event_id, content) VALUES
(1, 1, 'Hâte d''assister à cet événement !'),
(3, 2, 'Super organisation, j''ai adoré le concert !');