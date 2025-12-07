import React, { Component } from 'react';

class PropertyActions extends Component {

  static slug = 'et_pb_property_actions_widget';

  render() {
    const { className, display } = this.props;
    const isButtons = display === 'buttons';
    console.log(this.props);
    console.log(display);
    console.log(isButtons);

    return (
      <div className={className}>
        <div className="property_actions">
          {isButtons ? (
            <ul>
              <li><a>View Details</a></li>
              <li><a>Make Enquiry</a></li>
              <li><a>Print</a></li>
            </ul>
          ) : (
            <ul>
              <li>View Details</li>
              <li>Make Enquiry</li>
              <li>Print</li>
            </ul>
          )}
        </div>
      </div>
    );
  }
}

export default PropertyActions;
