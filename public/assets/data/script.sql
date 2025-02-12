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


CREATE TABLE promo (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_percentage DECIMAL(5, 2) CHECK (discount_percentage BETWEEN 0 AND 100),
    event_id INT REFERENCES events(id) ON DELETE CASCADE,
    usage_limit INT CHECK (usage_limit > 0),
    expiration_date TIMESTAMP NOT NULL
);

-- Reservations table
CREATE TABLE reservations (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    event_id INT REFERENCES events(id) ON DELETE CASCADE,
    ticket_type VARCHAR(50) NOT NULL CHECK (ticket_type IN ('VIP', 'PREMIUM', 'STANDART')),
    quantity INT CHECK (quantity > 0),
    total_price DECIMAL(10, 2) CHECK (total_price >= 0),
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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


CREATE TABLE categorys(
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255)
)

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

INSERT INTO events (id, title, artist_name, category, tags, date_start, date_end, location, price, capacity, organizer_id, status, isActif)
VALUES
(1, 'Concert of Legends', 'Band X', 'Music', '{"rock", "live"}', '2025-05-01 19:00:00', '2025-05-01 22:00:00', 'Stadium A', 100.00, 5000, 2, 'ACTIVE', TRUE),
(2, 'Jazz Night', 'Artist Y', 'Music', '{"jazz", "night"}', '2025-06-15 20:00:00', '2025-06-15 23:00:00', 'Jazz Club B', 50.00, 200, 2, 'ACTIVE', TRUE),
(3, 'Art Exhibition', 'Gallery Z', 'Art', '{"painting", "exhibition"}', '2025-07-10 10:00:00', '2025-07-10 18:00:00', 'Gallery Center', 20.00, 300, 2, 'ACTIVE', TRUE),
(4, 'Tech Conference 2025', 'Tech Co.', 'Conference', '{"technology", "conference"}', '2025-08-20 09:00:00', '2025-08-20 17:00:00', 'Convention Center', 250.00, 1000, 2, 'ACTIVE', TRUE),
(5, 'Food Festival', 'Food Corp', 'Festival', '{"food", "festival"}', '2025-09-05 11:00:00', '2025-09-05 20:00:00', 'Park C', 0.00, 10000, 2, 'ACTIVE', TRUE);

INSERT INTO promo (id, code, discount_percentage, event_id, usage_limit, expiration_date)
VALUES
(1, 'SUMMER20', 20.00, 2, 100, '2025-06-14 23:59:59'),
(2, 'TECH50', 50.00, 4, 50, '2025-08-19 23:59:59'),
(3, 'FOOD10', 10.00, 5, 200, '2025-09-04 23:59:59'),
(4, 'ART25', 25.00, 3, 30, '2025-07-09 23:59:59'),
(5, 'VIPDISCOUNT', 15.00, 2, 50, '2025-06-14 23:59:59');

INSERT INTO reservations (id, user_id, event_id, ticket_type, quantity, total_price, status)
VALUES
(2, 1, 2, 'early_bird', 3, 120.00, 'reserved'),
(3, 3, 3, 'paid', 1, 20.00, 'reserved'),
(5, 2, 5, 'VIP', 1, 50.00, 'reserved');

INSERT INTO payments (id, reservation_id, payment_method, transaction_id, amount, status)
VALUES
(2, 2, 'PayPal', 'TXN12346', 120.00, 'success'),
(3, 3, 'Debit Card', 'TXN12347', 20.00, 'pending'),
(5, 5, 'PayPal', 'TXN12349', 50.00, 'success');

INSERT INTO event_statistics (id, event_id, tickets_sold, revenue, participants_count)
VALUES
(1, 1, 500, 50000.00, 450),
(2, 2, 200, 10000.00, 150),
(3, 3, 100, 2000.00, 80),
(4, 4, 800, 200000.00, 750),
(5, 5, 10000, 50000.00, 9000);

INSERT INTO categorys (id, title, image)
VALUES
(1, 'Music', 'music_image.png'),
(2, 'Art', 'art_image.png'),
(3, 'Technology', 'tech_image.png'),
(4, 'Food', 'food_image.png'),
(5, 'Sports', 'sports_image.png');

INSERT INTO notifications (id, user_id, message, status)
VALUES
(1, 2, 'Your reservation for Jazz Night is confirmed!', 'not_read'),
(2, 2, 'VIP tickets for Tech Conference 2025 are now available.', 'not_read'),
(3, 3, 'Your payment for the Art Exhibition has been processed successfully.', 'read'),
(5, 2, 'Reminder: Your event starts in 2 days!', 'not_read');

INSERT INTO comments (id, user_id, event_id, content)
VALUES
(1, 1, 2, 'Can’t wait for this event!'),
(2, 2, 2, 'I hope there are enough tickets left for early bird.'),
(3, 3, 3, 'This art exhibition looks amazing!'),
(4, 2, 4, 'Looking forward to the tech conference, will there be a live stream?'),
(5, 2, 5, 'The food festival looks delicious, can’t wait to try the new dishes!');

-- Réinitialiser la séquence si nécessaire
ALTER SEQUENCE reservations_id_seq RESTART WITH 1;

INSERT INTO reservations (id, user_id, event_id, ticket_type, quantity, total_price, reservation_date)
VALUES
-- Réservations pour l'événement "Concert of Legends" (id: 1)
(1, 2, 1, 'VIP', 2, 250.00, '2025-01-15 10:30:00'),
(2, 3, 1, 'STANDART', 4, 400.00, '2025-01-16 14:20:00'),
(3, 1, 1, 'PREMIUM', 1, 150.00, '2025-01-17 09:15:00'),

-- Réservations pour "Jazz Night" (id: 2)
(4, 1, 2, 'VIP', 2, 120.00, '2025-02-01 11:00:00'),
(5, 2, 2, 'STANDART', 3, 150.00, '2025-02-02 16:45:00'),

-- Réservations pour "Art Exhibition" (id: 3)
(6, 3, 3, 'STANDART', 2, 40.00, '2025-03-05 13:20:00'),
(7, 1, 3, 'VIP', 1, 30.00, '2025-03-06 10:10:00'),

-- Réservations pour "Tech Conference 2025" (id: 4)
(8, 2, 4, 'PREMIUM', 2, 500.00, '2025-04-10 09:30:00'),
(9, 3, 4, 'VIP', 1, 300.00, '2025-04-11 14:15:00'),

-- Réservations pour "Food Festival" (id: 5)
(10, 1, 5, 'STANDART', 5, 0.00, '2025-05-01 12:00:00'),
(11, 2, 5, 'VIP', 2, 0.00, '2025-05-02 15:30:00'),
(12, 3, 5, 'PREMIUM', 3, 0.00, '2025-05-03 11:45:00');

SELECT SUM(r.quantity * e.price) AS total_revenue_global
FROM reservations r
JOIN events e ON r.event_id = e.id
WHERE r.status = 'reserved';

