import React, { Component } from 'react';

class PropertyActions extends Component {

    static slug = 'et_pb_property_actions_widget';

    render() {

        const {
            display,
            button_background_color,
            button_text_color,
            button_padding,
            button_margin,
        } = this.props;

        const isButtons = display === 'buttons';

        const ulStyle = isButtons
        ? {
            listStyleType: "none",
            margin: 0,
            padding: 0
            }
        : {};

        const liStyle = isButtons
        ? {
            display: "inline-block"
            }
        : {};

        const buttonStyle = isButtons
        ? {
            display: "block",
            backgroundColor: button_background_color,
            color: button_text_color,
            padding: button_padding,
            margin: button_margin,
            }
        : {};

        return (
            <div className="property_actions">
                <ul style={ulStyle}>
                    <li style={liStyle} className="action-make-enquiry"><a href="#" style={buttonStyle}>Make Enquiry</a></li>
                    <li style={liStyle} className="action-floorplans"><a href="#" style={buttonStyle}>Floorplan</a></li>
                    <li style={liStyle} className="action-brochure"><a href="#" style={buttonStyle}>View Brochure</a></li>
                    <li style={liStyle} className="action-epc"><a href="#" style={buttonStyle}>View EPC</a></li>
                </ul>
            </div>
        );
    }
}

export default PropertyActions;
