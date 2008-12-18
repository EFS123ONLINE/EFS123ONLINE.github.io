/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

package de.ilias.services.object;

import java.io.IOException;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Vector;

import org.apache.lucene.document.Document;

import de.ilias.services.db.DBFactory;
import de.ilias.services.lucene.index.CommandQueueElement;
import de.ilias.services.lucene.index.DocumentHandlerException;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class JDBCDataSource extends DataSource {

	String query;
	Vector<ParameterDefinition> parameters = new Vector<ParameterDefinition>();
	

	/**
	 * @param type
	 */
	public JDBCDataSource(int type) {

		super(type);
	}

	/**
	 * @return the query
	 */
	public String getQuery() {
		return query;
	}

	/**
	 * @param query the query to set
	 */
	public void setQuery(String query) {
		this.query = query;
	}

	/**
	 * @return the parameters
	 */
	public Vector<ParameterDefinition> getParameters() {
		return parameters;
	}

	/**
	 * @param parameters the parameters to set
	 */
	public void setParameters(Vector<ParameterDefinition> parameters) {
		this.parameters = parameters;
	}
	
	/**
	 * 
	 * @param parameter
	 */
	public void addParameter(ParameterDefinition parameter) {
		this.parameters.add(parameter);
	}

	/**
	 * @see java.lang.Object#toString()
	 */
	@Override
	public String toString() {
		
		StringBuffer out = new StringBuffer();
		
		out.append("New JDBC Data Source" );
		out.append("\n");
		out.append("Query: " + getQuery());
		out.append("\n");
		
		for(Object param : getParameters()) {
			
			out.append(param.toString());
		}
		out.append(super.toString());
		
		return out.toString();
	}
	
	/**
	 * 
	 * @see de.ilias.services.lucene.index.DocumentHandler#writeDocument(de.ilias.services.lucene.index.CommandQueueElement)
	 */
	public void writeDocument(CommandQueueElement el)
			throws DocumentHandlerException, IOException {

		logger.debug("Handling data source: " + getType());
		
		try {
			// Create Statement for JDBC datasource
			PreparedStatement pst = DBFactory.factory().prepareStatement(getQuery());

			int paramNumber = 1;
			for(Object param : getParameters()) {
				
				((ParameterDefinition) param).writeParameter(pst,paramNumber++,el);
			}
			ResultSet res = pst.executeQuery();
			while(res.next()) {
				
				logger.debug("Found new result");
				for(Object field : getFields()) {
					((FieldDefinition) field).writeDocument(res);
				}
				// TODO: docHolder.newDocument() ?!
			}
			
		} 
		catch (SQLException e) {
			logger.error("Cannot parse data source.");
			throw new DocumentHandlerException(e);
		}
	}
}