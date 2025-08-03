-- Database Updates for View Proposals & Deal Management Module
-- Run these queries to add the required tables and columns

-- Update proposals table to add status column (if not exists)
ALTER TABLE proposals 
ADD COLUMN IF NOT EXISTS status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending';

-- Create deals table
CREATE TABLE IF NOT EXISTS deals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proposal_id INT NOT NULL,
    client_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    status ENUM('ongoing', 'completed', 'cancelled') DEFAULT 'ongoing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deal_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    file VARCHAR(255),
    seen BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_proposals_status ON proposals(status);
CREATE INDEX IF NOT EXISTS idx_proposals_project_id ON proposals(project_id);
CREATE INDEX IF NOT EXISTS idx_proposals_freelancer_id ON proposals(freelancer_id);
CREATE INDEX IF NOT EXISTS idx_deals_client_id ON deals(client_id);
CREATE INDEX IF NOT EXISTS idx_deals_freelancer_id ON deals(freelancer_id);
CREATE INDEX IF NOT EXISTS idx_deals_status ON deals(status);
CREATE INDEX IF NOT EXISTS idx_messages_deal_id ON messages(deal_id);
CREATE INDEX IF NOT EXISTS idx_messages_sender_id ON messages(sender_id);
CREATE INDEX IF NOT EXISTS idx_messages_created_at ON messages(created_at);

-- Insert sample data for testing (optional)
-- Sample proposal statuses
UPDATE proposals SET status = 'pending' WHERE status IS NULL;

-- Sample deals (if you have existing proposals)
-- INSERT INTO deals (proposal_id, client_id, freelancer_id, status) 
-- SELECT id, 
--        (SELECT client_id FROM projects WHERE id = proposals.project_id),
--        freelancer_id, 
--        'ongoing' 
-- FROM proposals 
-- WHERE status = 'accepted' 
-- LIMIT 1; 