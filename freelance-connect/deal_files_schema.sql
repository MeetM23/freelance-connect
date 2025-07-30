-- Database Schema for Deal Files (Non-Chat Version)
-- Run these queries to add the required table

-- Create deal_files table
CREATE TABLE IF NOT EXISTS deal_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deal_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_deal_files_deal_id ON deal_files(deal_id);
CREATE INDEX IF NOT EXISTS idx_deal_files_uploaded_by ON deal_files(uploaded_by);
CREATE INDEX IF NOT EXISTS idx_deal_files_uploaded_at ON deal_files(uploaded_at);

-- Optional: Add some sample data for testing
-- INSERT INTO deal_files (deal_id, uploaded_by, file_name, file_path) VALUES
-- (1, 2, 'project_deliverable.zip', 'uploads/deals/deal_1_2_1234567890.zip'),
-- (1, 2, 'documentation.pdf', 'uploads/deals/deal_1_2_1234567891.pdf'); 