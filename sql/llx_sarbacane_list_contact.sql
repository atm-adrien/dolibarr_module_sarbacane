-- <MailChimp connector>
-- Copyright (C) 2021 Quentin Vial-Gouteyron quentin.vial-gouteyron@atm-consulting.fr
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

CREATE TABLE IF NOT EXISTS llx_sarbacane_list_contact (
	rowid integer NOT NULL auto_increment PRIMARY KEY,
	fk_contact integer NOT NULL,
	sarbacane_listid varchar(200),
	sarbacane_contactlistid varchar(200),
	fk_user_author	integer	NOT NULL,
	datec	datetime  NOT NULL,
	fk_user_mod integer NOT NULL,
	tms timestamp
)ENGINE=InnoDB;
